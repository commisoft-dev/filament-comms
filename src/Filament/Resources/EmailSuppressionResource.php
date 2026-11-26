<?php

namespace Commisoft\FilamentComms\Filament\Resources;

use BackedEnum;
use Commisoft\FilamentComms\Filament\Resources\EmailSuppressionResource\Pages\ListEmailSuppressions;
use Commisoft\FilamentComms\Models\EmailSuppression;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EmailSuppressionResource extends Resource
{
    protected static ?string $model = EmailSuppression::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $navigationLabel = 'Suppressions & Bounces';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('filament-comms.navigation_group', 'Communications');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Select::make('reason')
                ->options([
                    'hard_bounce' => 'Hard Bounce',
                    'spam_complaint' => 'Spam Complaint',
                    'manual_block' => 'Manual Block',
                ])
                ->default('manual_block')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable()->weight('bold'),
                TextColumn::make('reason')->badge()->color('danger'),
                TextColumn::make('created_at')->dateTime('d M Y H:i'),
            ])
            ->recordActions([
                DeleteAction::make()->label('Remove Suppression'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailSuppressions::route('/'),
        ];
    }
}
