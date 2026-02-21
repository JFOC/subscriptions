<?php

declare(strict_types=1);

namespace Crumbls\Subscriptions\Traits;

use Carbon\Carbon;
use Crumbls\Subscriptions\Models\Plan;
use Crumbls\Subscriptions\Models\PlanSubscription;
use Crumbls\Subscriptions\Services\Period;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPlanSubscriptions
{
    public function planSubscriptions(): MorphMany
    {
        return $this->morphMany(
            config('subscriptions.models.plan_subscription'),
            'subscriber',
            'subscriber_type',
            'subscriber_id',
        );
    }

    public function activePlanSubscriptions(): Collection
    {
        return $this->planSubscriptions->reject->inactive();
    }

    public function planSubscription(string $subscriptionSlug): ?PlanSubscription
    {
        return $this->planSubscriptions()->where('slug', $subscriptionSlug)->first();
    }

    public function subscribedPlans(): Collection
    {
        $planIds = $this->planSubscriptions->reject->inactive()->pluck('plan_id')->unique();

        $model = config('subscriptions.models.plan');

        return $model::whereIn('id', $planIds)->get();
    }

    public function subscribedTo(int $planId): bool
    {
        $subscription = $this->planSubscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    public function newPlanSubscription(string $subscription, Plan $plan, ?Carbon $startDate = null): PlanSubscription
    {
        $trial = new Period($plan->trial_interval ?? 'day', $plan->trial_period ?? 0, $startDate ?? now());
        $period = new Period($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

        return $this->planSubscriptions()->create([
            'name' => $subscription,
            'plan_id' => $plan->getKey(),
            'trial_ends_at' => $plan->hasTrial() ? $trial->getEndDate() : null,
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ]);
    }
}
