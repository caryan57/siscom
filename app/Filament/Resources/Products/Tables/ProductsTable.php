<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('baseVariant.barcode')
                    ->label('Código')
                    ->searchable(),
                TextColumn::make('baseVariant.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('baseVariant.defaultPrice.price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('baseVariant.status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => is_object($state) && method_exists($state, 'getLabel') ? $state->getLabel() : ($state ? 'Activo' : 'Inactivo'))
                    ->color(fn ($state): string => is_object($state) && method_exists($state, 'getColor') ? $state->getColor() : ($state ? 'success' : 'danger')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
