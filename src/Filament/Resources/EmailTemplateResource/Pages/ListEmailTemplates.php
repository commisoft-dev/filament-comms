<?php

namespace Commisoft\FilamentComms\Filament\Resources\EmailTemplateResource\Pages;

use Commisoft\FilamentComms\Filament\Resources\EmailTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
