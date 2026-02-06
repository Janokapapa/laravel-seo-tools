<?php

namespace JanDev\SeoTools\Filament\Components;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;

class SeoFieldsSection
{
    public static function make(): Section
    {
        return Section::make('SEO')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsed()
            ->schema([
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(60)
                    ->helperText('Max 60 characters. Leave empty to use the title field.')
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->maxLength(160)
                    ->helperText('Max 160 characters. Leave empty to use the description field.')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->helperText('Comma-separated keywords')
                    ->columnSpanFull(),
                TextInput::make('og_image')
                    ->label('OG Image URL')
                    ->url()
                    ->helperText('Open Graph image URL for social sharing')
                    ->columnSpanFull(),
            ]);
    }
}
