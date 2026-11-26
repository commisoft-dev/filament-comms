<?php

namespace Commisoft\FilamentComms\Filament\Resources;

use BackedEnum;
use Commisoft\FilamentComms\Filament\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;
use Commisoft\FilamentComms\Filament\Resources\EmailTemplateResource\Pages\EditEmailTemplate;
use Commisoft\FilamentComms\Filament\Resources\EmailTemplateResource\Pages\ListEmailTemplates;
use Commisoft\FilamentComms\Models\EmailTemplate;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $navigationLabel = 'Email Templates';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return config('filament-comms.navigation_group', 'Communications');
    }

    public static function form(Schema $schema): Schema
    {
        $categories = collect(config('filament-comms.categories', []))
            ->mapWithKeys(fn($meta, $key) => [$key => $meta['label'] ?? ucfirst($key)])
            ->all();

        return $schema->components([
            Section::make('Template Details')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, callable $set) => $set('slug', Str::slug((string) $state))),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Select::make('category')
                        ->options($categories)
                        ->default('newsletters')
                        ->required(),
                    Toggle::make('is_active')
                        ->default(true),
                    TextInput::make('subject')
                        ->required()
                        ->columnSpanFull(),
                    TextInput::make('preheader')
                        ->columnSpanFull(),
                    RichEditor::make('body_html')
                        ->label('Body HTML')
                        ->required()
                        ->helperText('Tokens: {{first_name}}, {{name}}, {{email}}, {{brand_name}}, {{preferences_url}}')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => config("filament-comms.categories.{$state}.label", ucfirst($state))),
                TextColumn::make('subject')->limit(45),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime('d M Y'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailTemplates::route('/'),
            'create' => CreateEmailTemplate::route('/create'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
