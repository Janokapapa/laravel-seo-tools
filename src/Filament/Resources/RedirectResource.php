<?php

namespace JanDev\SeoTools\Filament\Resources;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use JanDev\SeoTools\Models\Redirect;
use JanDev\SeoTools\Filament\Resources\RedirectResource\Pages;
use UnitEnum;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    protected static string|UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source_path')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Path to redirect FROM (e.g., /old-page)')
                    ->prefix('/'),
                TextInput::make('destination_path')
                    ->required()
                    ->helperText('Path to redirect TO (e.g., /new-page or https://...)'),
                Select::make('status_code')
                    ->options([
                        301 => '301 - Permanent Redirect',
                        302 => '302 - Temporary Redirect',
                    ])
                    ->default(301)
                    ->required()
                    ->native(false),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_path')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination_path')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('status_code')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        301 => 'success',
                        302 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('hits')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('last_hit_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->defaultSort('hits', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
