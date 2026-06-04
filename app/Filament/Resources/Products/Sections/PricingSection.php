<?php

namespace App\Filament\Resources\Products\Sections;

use App\Models\PriceList;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;


class PricingSection
{
    public static function make(): Section
    {
        $priceListFields = PriceList::query()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->map(function (PriceList $priceList) {
                return TextInput::make("prices.{$priceList->id}")
                    ->label($priceList->name)
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->default(0)
                    ->required($priceList->is_default);
            })
            ->toArray();
        return
            Section::make('Precios')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                TextInput::make('cost')
                    ->label('Costo')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->minValue(0),
                Fieldset::make()
                    ->label('Listas de precios')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])
                    ->schema([...$priceListFields])
            ])
            ->hidden(fn(Get $get): bool => $get('has_variants'))
            ->columns(1)
            ->columnSpan(12);
    }
}
