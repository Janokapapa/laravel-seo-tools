<?php

namespace JanDev\SeoTools\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use JanDev\SeoTools\Models\SeoPage;
use JanDev\SeoTools\Filament\Resources\SeoPageResource\Pages;

class SeoPageResource extends Resource
{
    protected static ?string $model = SeoPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('route_name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Laravel route name (e.g., "home", "contact")')
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->maxLength(60)
                    ->helperText('Max 60 characters'),
                TextInput::make('og_title')
                    ->label('OG Title')
                    ->maxLength(60),
                Textarea::make('meta_description')
                    ->maxLength(160)
                    ->helperText('Max 160 characters')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('og_description')
                    ->label('OG Description')
                    ->maxLength(200)
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('meta_keywords')
                    ->helperText('Comma-separated')
                    ->columnSpanFull(),
                TextInput::make('og_image')
                    ->label('OG Image URL')
                    ->url()
                    ->columnSpanFull(),
                Select::make('schema_type')
                    ->options([
                        'WebPage' => 'WebPage',
                        'AboutPage' => 'AboutPage',
                        'ContactPage' => 'ContactPage',
                        'FAQPage' => 'FAQPage',
                        'CollectionPage' => 'CollectionPage',
                    ])
                    ->native(false),
                Toggle::make('is_indexable')
                    ->default(true)
                    ->helperText('Uncheck to add noindex, nofollow'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('route_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('meta_description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_indexable')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('route_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoPages::route('/'),
            'create' => Pages\CreateSeoPage::route('/create'),
            'edit' => Pages\EditSeoPage::route('/{record}/edit'),
        ];
    }
}
