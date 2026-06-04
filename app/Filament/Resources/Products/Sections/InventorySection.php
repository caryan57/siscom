<?php

namespace App\Filament\Resources\Products\Sections;

use App\Models\Branch;
use App\Models\Unit;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;

class InventorySection
{
    public static function make(): Section
    {
        return Section::make('Inventario')
            ->description('Asigna el inventario inicial del producto por sucursal. Al activar lotes el inventario se gestiona en el modulo Lotes')
            ->schema([
                Grid::make(3)
                    ->schema([
                        Toggle::make('track_inventory')
                            ->label('Controlar inventario')
                            ->live()
                            ->default(true)
                            ->dehydrated(),
                        Toggle::make('track_batches')
                            ->label('Controlar lotes')
                            ->live()
                            ->default(false)
                            ->disabled(fn(Get $get) => !$get('track_inventory'))
                            ->dehydrated(),
                        Toggle::make('track_expiration')
                            ->label('Controlar caducidad')
                            ->live()
                            ->default(false)
                            ->disabled(fn(Get $get) => !$get('track_batches'))
                            ->dehydrated(),
                    ]),
                Repeater::make('branch_stocks')
                    ->label('Inventario por sucursal')
                    ->hidden(fn(Get $get) => !$get('track_inventory') || $get('has_variants') || $get('track_batches'))
                    ->schema([
                        Hidden::make('branch_id'),
                        TextInput::make('branch_name')
                            ->label('Sucursal')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('stock')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('min_stock')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('location')
                            ->label('Ubicación')
                            ->placeholder('Ej. Pasillo A, Rack 2')
                    ])
                    ->default(function () {
                        return Branch::query()
                            ->where('company_id', current_company_id())
                            ->get()
                            ->map(fn($branch) => [
                                'branch_id' => $branch->id,
                                'branch_name' => $branch->name,
                                'stock' => 0,
                                'min_stock' => 0,
                            ])
                            ->toArray();
                    })
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columns(4),
            ])
            ->hidden(fn(Get $get): bool => $get('has_variants') || Unit::isService($get('unit_id')))
            ->columns(1)
            ->columnSpan(12);
    }

    public static function getBranches(): array
    {
        return Branch::query()
            ->where('company_id', current_company_id())
            ->pluck('name', 'id')
            ->all();
    }
}
