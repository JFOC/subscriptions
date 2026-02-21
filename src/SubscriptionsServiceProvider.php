<?php

declare(strict_types=1);

namespace Crumbls\Subscriptions;

use Crumbls\Subscriptions\Console\PruneExpiredSubscriptionsCommand;
use Illuminate\Support\ServiceProvider;

class SubscriptionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/subscriptions.php', 'subscriptions');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/subscriptions.php' => config_path('subscriptions.php'),
            ], 'subscriptions-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'subscriptions-migrations');

            $this->commands([
                PruneExpiredSubscriptionsCommand::class,
            ]);
        }

        if (config('subscriptions.autoload_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
