<?php

namespace Commisoft\FilamentComms\Filament\Resources;

use BackedEnum;
use Commisoft\FilamentComms\Contracts\AudienceResolver;
use Commisoft\FilamentComms\Filament\Resources\EmailCampaignResource\Pages\CreateEmailCampaign;
use Commisoft\FilamentComms\Filament\Resources\EmailCampaignResource\Pages\EditEmailCampaign;
use Commisoft\FilamentComms\Filament\Resources\EmailCampaignResource\Pages\ListEmailCampaigns;
use Commisoft\FilamentComms\Models\EmailCampaign;
use Commisoft\FilamentComms\Models\EmailTemplate;
use Commisoft\FilamentComms\Services\CampaignDispatcher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Email Campaigns';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('filament-comms.navigation_group', 'Communications');
    }

    public static function form(Schema $schema): Schema
    {
        $categories = collect(config('filament-comms.categories', []))
            ->mapWithKeys(fn($meta, $key) => [$key => $meta['label'] ?? ucfirst($key)])
            ->all();

        $segments = app(AudienceResolver::class)->segments();

        return $schema->components([
            Section::make('Campaign Setup')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g. November Pro Upgrade Promo'),
                    Select::make('category')
                        ->options($categories)
                        ->default('newsletters')
                        ->required(),
                    Select::make('template_id')
                        ->label('Load from Template (Optional)')
                        ->options(fn() => EmailTemplate::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) {
                                return;
                            }
                            $tpl = EmailTemplate::find($state);
                            if ($tpl) {
                                $set('category', $tpl->category);
                                $set('subject', $tpl->subject);
                                $set('preheader', $tpl->preheader);
                                $set('body_html', $tpl->body_html);
                            }
                        }),
                    DateTimePicker::make('scheduled_at')
                        ->label('Schedule Send (Optional)')
                        ->nullable(),
                ]),

            Section::make('Audience')
                ->columns(2)
                ->schema([
                    Select::make('audience_filter.segment')
                        ->label('Target Segment')
                        ->options($segments)
                        ->default(array_key_first($segments) ?? 'all_users')
                        ->required(),
                    Textarea::make('audience_filter.manual_emails')
                        ->label('Additional / Manual Emails (Optional)')
                        ->rows(3)
                        ->placeholder('Comma or newline separated emails...'),
                ]),

            Section::make('Email Content')
                ->schema([
                    TextInput::make('subject')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Supports {{first_name}}, {{name}}, {{brand_name}}'),
                    TextInput::make('preheader')
                        ->label('Preview Text (Preheader)')
                        ->maxLength(255),
                    RichEditor::make('body_html')
                        ->label('Email Body')
                        ->required()
                        ->helperText('Available tokens: {{first_name}}, {{name}}, {{email}}, {{brand_name}}, {{preferences_url}}')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => config("filament-comms.categories.{$state}.label", ucfirst($state))),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sent' => 'success',
                        'sending' => 'info',
                        'scheduled' => 'warning',
                        'paused', 'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('sent_count')
                    ->label('Sent')
                    ->numeric(),
                TextColumn::make('skipped_count')
                    ->label('Unsubscribed/Skipped')
                    ->numeric(),
                TextColumn::make('failed_count')
                    ->label('Failed')
                    ->numeric(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'sending' => 'Sending',
                        'sent' => 'Sent',
                        'paused' => 'Paused',
                    ]),
            ])
            ->recordActions([
                Action::make('sendTest')
                    ->label('Test')
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->form([
                        TextInput::make('test_email')
                            ->label('Send Test To')
                            ->email()
                            ->default(fn() => auth()->user()?->email)
                            ->required(),
                    ])
                    ->action(function (EmailCampaign $record, array $data) {
                        app(CampaignDispatcher::class)->sendTest($record, $data['test_email'], auth()->user()?->name ?? 'Admin');
                        Notification::make()->title('Test email sent to ' . $data['test_email'])->success()->send();
                    }),

                Action::make('dispatch')
                    ->label('Send Now')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn(EmailCampaign $record) => in_array($record->status, ['draft', 'scheduled'], true))
                    ->requiresConfirmation()
                    ->modalHeading('Send Email Campaign')
                    ->modalDescription(function (EmailCampaign $record) {
                        $stats = app(CampaignDispatcher::class)->countEligibleRecipients($record);
                        return "Eligible recipients: {$stats['eligible']} (Skipped due to unsubscribe/suppression: {$stats['skipped']}). Proceed?";
                    })
                    ->action(function (EmailCampaign $record) {
                        $queued = app(CampaignDispatcher::class)->dispatch($record);
                        Notification::make()->title("Campaign queued for {$queued} recipient(s).")->success()->send();
                    }),

                EditAction::make()
                    ->visible(fn(EmailCampaign $record) => in_array($record->status, ['draft', 'scheduled'], true)),
                DeleteAction::make()
                    ->visible(fn(EmailCampaign $record) => $record->status === 'draft'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailCampaigns::route('/'),
            'create' => CreateEmailCampaign::route('/create'),
            'edit' => EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
