<?php

namespace Commisoft\FilamentComms\Filament\Resources\EmailSuppressionResource\Pages;

use Commisoft\FilamentComms\Filament\Resources\EmailSuppressionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailSuppressions extends ListRecords
{
    protected static string $resource = EmailSuppressionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Block Email'),
        ];
    }
}
