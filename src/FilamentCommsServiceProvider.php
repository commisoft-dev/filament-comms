<?php

namespace Commisoft\FilamentComms;

use Commisoft\FilamentComms\Contracts\AudienceResolver;
use Illuminate\Support\ServiceProvider;

class FilamentCommsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-comms.php', 'filament-comms');

        $this->app->bind(AudienceResolver::class, function ($app) {
            $resolverClass = config('filament-comms.audience_resolver');
            return $app->make($resolverClass);
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-comms');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/filament-comms.php' => config_path('filament-comms.php'),
            ], 'filament-comms-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-comms'),
            ], 'filament-comms-views');
        }
    }
}
