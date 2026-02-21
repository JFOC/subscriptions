# Crumbls Subscriptions

A flexible plans and subscription management system for Laravel. Manage SaaS plans, features, and subscriber usage tracking without coupling to any payment provider.

> **Inspired by** [rinvex/laravel-subscriptions](https://github.com/rinvex/laravel-subscriptions), which is now abandoned. This package modernizes the codebase for Laravel 11/12, PHP 8.2+, and current Laravel conventions.

## Features

- Plan management with trial, grace, and invoice periods
- Feature-based usage tracking with automatic resets
- Polymorphic subscriptions — attach to any model
- Grace period support — subscriptions stay active during grace window
- Lifecycle events — hook into created, canceled, renewed, plan changed
- Translatable plan names and descriptions (via Spatie)
- Sortable plans and features (via Spatie)
- Configurable table names and swappable models
- Artisan command to prune expired subscriptions
- PHPStan level 5 clean

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require crumbls/subscriptions
```

Publish config and migrations (optional):

```bash
php artisan vendor:publish --tag=subscriptions-config
php artisan vendor:publish --tag=subscriptions-migrations
```

Run migrations:

```bash
php artisan migrate
```

Migrations autoload by default. Set `autoload_migrations` to `false` in `config/subscriptions.php` to disable this.

## Usage

### Add subscriptions to a model

```php
use Crumbls\Subscriptions\Traits\HasPlanSubscriptions;

class User extends Authenticatable
{
    use HasPlanSubscriptions;
}
```

Works on any Eloquent model — not just User.

### Create a plan

```php
use Crumbls\Subscriptions\Models\Plan;

$plan = Plan::create([
    'name' => 'Pro',
    'description' => 'Pro plan',
    'price' => 9.99,
    'signup_fee' => 1.99,
    'invoice_period' => 1,
    'invoice_interval' => 'month',
    'trial_period' => 15,
    'trial_interval' => 'day',
    'grace_period' => 7,
    'grace_interval' => 'day',
    'currency' => 'USD',
]);
```

Intervals accept: `hour`, `day`, `week`, `month`, `year`.

### Add features to a plan

```php
$plan->features()->create([
    'name' => 'API Requests',
    'slug' => 'api-requests',
    'value' => '1000',
    'resettable_period' => 1,
    'resettable_interval' => 'month',
]);

// Boolean features
$plan->features()->create([
    'name' => 'SSL',
    'slug' => 'ssl',
    'value' => 'true', // always available
]);
```

### Subscribe a user

```php
$user->newPlanSubscription('main', $plan);
```

### Check subscription status

```php
$user->subscribedTo($plan->id);               // bool
$subscription = $user->planSubscription('main');

$subscription->active();       // true if not ended, or on trial/grace
$subscription->onTrial();      // currently in trial period
$subscription->onGracePeriod(); // ended but within grace window
$subscription->canceled();     // has been canceled
$subscription->ended();        // period has expired
$subscription->inactive();     // opposite of active
```

### Feature usage

```php
$subscription->recordFeatureUsage('api-requests');
$subscription->recordFeatureUsage('api-requests', 5);           // add 5
$subscription->recordFeatureUsage('api-requests', 10, false);   // set to 10
$subscription->reduceFeatureUsage('api-requests', 3);
$subscription->canUseFeature('api-requests');      // bool
$subscription->getFeatureUsage('api-requests');     // int
$subscription->getFeatureRemainings('api-requests'); // int
$subscription->getFeatureValue('api-requests');     // raw value
```

### Plan changes

```php
$subscription->changePlan($newPlan);
$subscription->renew();
$subscription->cancel();          // cancel at end of period
$subscription->cancel(true);      // cancel immediately
```

### Scopes

```php
use Crumbls\Subscriptions\Models\PlanSubscription;

PlanSubscription::findActive()->get();
PlanSubscription::findEndingPeriod(7)->get();
PlanSubscription::findEndedPeriod()->get();
PlanSubscription::findEndingTrial(3)->get();
PlanSubscription::findEndedTrial()->get();
PlanSubscription::ofSubscriber($user)->get();
PlanSubscription::byPlanId($plan->id)->get();
```

### Events

All lifecycle actions dispatch events you can listen to:

| Event | Fired when |
|---|---|
| `SubscriptionCreated` | A new subscription is created |
| `SubscriptionCanceled` | A subscription is canceled (includes `$immediate` flag) |
| `SubscriptionRenewed` | A subscription is renewed |
| `SubscriptionPlanChanged` | A subscription switches plans (includes `$oldPlan` and `$newPlan`) |

```php
use Crumbls\Subscriptions\Events\SubscriptionCreated;

class SendWelcomeEmail
{
    public function handle(SubscriptionCreated $event): void
    {
        $event->subscription->subscriber->notify(/* ... */);
    }
}
```

### Pruning expired subscriptions

```bash
php artisan subscriptions:prune              # soft-deletes canceled subs older than 30 days
php artisan subscriptions:prune --days=90    # custom threshold
php artisan subscriptions:prune --force      # skip confirmation
```

## Configuration

Publish the config to customize table names or swap model classes:

```php
// config/subscriptions.php
return [
    'autoload_migrations' => true,
    'tables' => [
        'plans' => 'plans',
        'plan_features' => 'plan_features',
        'plan_subscriptions' => 'plan_subscriptions',
        'plan_subscription_usage' => 'plan_subscription_usage',
    ],
    'models' => [
        'plan' => \Crumbls\Subscriptions\Models\Plan::class,
        'plan_feature' => \Crumbls\Subscriptions\Models\PlanFeature::class,
        'plan_subscription' => \Crumbls\Subscriptions\Models\PlanSubscription::class,
        'plan_subscription_usage' => \Crumbls\Subscriptions\Models\PlanSubscriptionUsage::class,
    ],
];
```

### Extending / Overriding Models

Every model in this package is resolved through config, so you can swap in your own. Extend the base model, then update `config/subscriptions.php`:

```php
// app/Models/Plan.php
namespace App\Models;

use Crumbls\Subscriptions\Models\Plan as BasePlan;

class Plan extends BasePlan
{
    public function cancel(bool $immediately = false): static
    {
        // Cancel the recurring payment with your payment provider
        $this->subscriber->paymentProvider()->cancelSubscription($this);

        return parent::cancel($immediately);
    }
}
```

```php
// config/subscriptions.php
'models' => [
    'plan' => \App\Models\Plan::class,
    // ...
],
```

All relationships, scopes, traits, and the prune command resolve models through config — your custom models will be used everywhere automatically. No container bindings or service provider changes needed.

### Scheduling the pruner

```php
// routes/console.php or bootstrap/app.php
Schedule::command('subscriptions:prune --force')->daily();
```

## Considerations

- **Payments are out of scope.** This package handles plan/subscription logic only. Integrate with Stripe, Paddle, etc. separately.
- **Translatable fields**: `name` and `description` on plans, features, and subscriptions are stored as JSON and support multiple locales via [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable).
- **Soft deletes**: All models use soft deletes. The prune command only soft-deletes; use `forceDelete()` if you need permanent removal.

## License

MIT
