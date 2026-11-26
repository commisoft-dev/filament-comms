<?php

namespace Commisoft\FilamentComms;

use Commisoft\FilamentComms\Filament\Resources\EmailCampaignResource;
use Commisoft\FilamentComms\Filament\Resources\EmailPreferenceResource;
use Commisoft\FilamentComms\Filament\Resources\EmailSuppressionResource;
use Commisoft\FilamentComms\Filament\Resources\EmailTemplateResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentCommsPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-comms';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            EmailCampaignResource::class,
            EmailTemplateResource::class,
            EmailPreferenceResource::class,
            EmailSuppressionResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
