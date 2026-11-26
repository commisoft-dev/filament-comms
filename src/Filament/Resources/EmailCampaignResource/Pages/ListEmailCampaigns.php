<?php

namespace Commisoft\FilamentComms\Filament\Resources\EmailCampaignResource\Pages;

use Commisoft\FilamentComms\Filament\Resources\EmailCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailCampaigns extends ListRecords
{
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
