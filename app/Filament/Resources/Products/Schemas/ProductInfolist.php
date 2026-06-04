<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información general')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),
                        TextEntry::make('baseVariant.barcode')
                            ->label('Código'),
                        TextEntry::make('baseVariant.sku')
                            ->label('SKU')
                            ->placeholder('-'),
                        TextEntry::make('baseVariant.cost')
                            ->label('Costo')
                            ->money('MXN'),
                        ImageEntry::make('image')
                            ->label('Imagen')
                            ->placeholder('-'),
                        TextEntry::make('category.name')
                            ->label('Categoría'),
                        TextEntry::make('brand.name')
                            ->label('Marca'),
                        TextEntry::make('unit.name')
                            ->label('Unidad'),
                        TextEntry::make('tax.name')
                            ->label('Impuesto'),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
                Section::make('Precios de venta')
                    ->schema([
                        RepeatableEntry::make('baseVariant.prices')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                TextEntry::make('priceList.name')
                                    ->label('Nombre'),
                                TextEntry::make('price')
                                    ->label('Precio')
                                    ->money('MXN'),
                            ])
                            ->columns(2),
                    ]),
                Section::make('Estado y fechas de registro')
                    ->schema([
                        TextEntry::make('baseVariant.status')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn ($state): string => is_object($state) && method_exists($state, 'getLabel') ? $state->getLabel() : ($state ? 'Activo' : 'Inactivo'))
                            ->color(fn ($state): string => is_object($state) && method_exists($state, 'getColor') ? $state->getColor() : ($state ? 'success' : 'danger')),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
