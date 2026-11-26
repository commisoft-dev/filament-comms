<?php

namespace Commisoft\FilamentComms\Filament\Resources;

use BackedEnum;
use Commisoft\FilamentComms\Filament\Resources\EmailPreferenceResource\Pages\ListEmailPreferences;
use Commisoft\FilamentComms\Models\EmailPreference;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class EmailPreferenceResource extends Resource
{
    protected static ?string $model = EmailPreference::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Subscribers & Opt-Outs';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('filament-comms.navigation_group', 'Communications');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable()->sortable()->weight('bold'),
                IconColumn::make('unsubscribed_all')
                    ->label('Unsubscribed All')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('unsubscribe_reason')
                    ->label('Reason')
                    ->placeholder('—')
                    ->limit(40),
                TextColumn::make('unsubscribed_at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                TernaryFilter::make('unsubscribed_all')->label('Unsubscribed All'),
            ])
            ->recordActions([
                Action::make('resubscribe')
                    ->label('Re-subscribe')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn(EmailPreference $record) => $record->unsubscribed_all)
                    ->requiresConfirmation()
                    ->action(function (EmailPreference $record) {
                        $defaults = collect(config('filament-comms.categories', []))
                            ->mapWithKeys(fn($meta, $key) => [$key => (bool) ($meta['default'] ?? true)])
                            ->all();

                        $record->update([
                            'unsubscribed_all' => false,
                            'unsubscribed_at' => null,
                            'preferences' => $defaults,
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailPreferences::route('/'),
        ];
    }
}
