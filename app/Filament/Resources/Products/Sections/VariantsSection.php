<?php

namespace App\Filament\Resources\Products\Sections;

use App\Models\Attribute;
use App\Models\Branch;
use App\Models\PriceList;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

class VariantsSection
{
    public static function make(): Section
    {
        return Section::make('Variantes')
            ->description('Genera las variantes de tu producto')
            ->schema([
                self::globalConfigurationSection(),
                Section::make('Agregar atributos')
                    ->description('Agrega los atributos de tu producto para generar las variantes')
                    ->schema([
                        Select::make('variant_attribute_form.attribute_id')
                            ->label('Atributo')
                            ->options(function ($get) {
                                $usedAttributesIds = collect($get('variant_attributes') ?? [])
                                    ->pluck('attribute_id')
                                    ->toArray();

                                return self::getAttributes($usedAttributesIds);
                            })
                            ->live()
                            ->searchable(),
                        TextInput::make('variant_attribute_form.values_input')
                            ->label('Valores')
                            ->placeholder('Azul, Amarillo, Negro')
                            ->live(),
                        Actions::make([
                            Action::make('addVariantAttribute')
                                ->label('Agregar atributo')
                                ->button()
                                ->action(fn($livewire) => $livewire->addVariantAttribute()),
                        ])
                            ->verticallyAlignEnd(true),
                        ViewField::make('variant_attributes_summary')
                            ->view('filament.products.variant-attributes-summary')
                            ->viewData([
                                'attributes' => fn(Get $get) => $get('variant_attributes') ?? [],
                                'generatedVariants' => fn(Get $get) => $get('generated_variants') ?? [],
                                'priceLists' => PriceList::query()->orderByDesc('is_default')->orderBy('id')->get(),
                            ]),
                        Hidden::make('variant_attributes')->default([]),
                    ])->collapsed(false),
                Section::make('Variantes generadas')
                    ->schema([
                        EmptyState::make('No hay variantes')
                            ->icon('heroicon-o-cube-transparent')
                            ->description('Crea atributos para generar las variantes de tu producto')
                            ->hidden(fn(Get $get) => !empty($get('generated_variants'))),
                        ViewField::make('generated_variants_table')
                            ->view('filament.products.generated-variants-table')
                            ->viewData(fn(Get $get) => [
                                'attributes' => $get('variant_attributes') ?? [],
                                'generatedVariants' => $get('generated_variants') ?? [],
                                'priceLists' => PriceList::query()->orderByDesc('is_default')->orderBy('id')->get(),
                                'branches' => Branch::query()->orderBy('name')->get(),
                            ])
                            ->hidden(fn(Get $get) => empty($get('generated_variants'))),
                    ]),
                Hidden::make('generated_variants')->default([]),
            ])
            ->hidden(fn(Get $get): bool => !$get('has_variants'))
            ->columns(1)
            ->columnSpan(12);
    }

    public static function getAttributes(array $excludeIds = []): array
    {
        return Attribute::query()
            ->where('company_id', current_company_id())
            ->when(filled($excludeIds), fn($query) => $query->whereNotIn('id', $excludeIds))
            ->pluck('name', 'id')
            ->all();
    }

    protected static function globalConfigurationSection(): Section
    {
        $priceListFields = PriceList::query()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->map(function (PriceList $priceList) {
                return TextInput::make("variant_defaults.prices.{$priceList->id}")
                    ->label($priceList->name)
                    ->numeric()
                    ->default(0)
                    ->prefix('$');
            })
            ->toArray();

        $branchStockFields = Branch::query()
            ->orderBy('name')
            ->get()
            ->map(function (Branch $branch) {
                return TextInput::make("variant_defaults.branch_stocks.{$branch->id}.stock")
                    ->label($branch->name)
                    ->numeric()
                    ->default(0);
            })
            ->toArray();

        return Section::make('Configuración global')
            ->description('Configura los valores generales para todas las variantes')
            ->schema([
                Grid::make()
                    ->schema([
                        Section::make('Precios')
                            ->icon('heroicon-o-currency-dollar')
                            ->description('Establece los precios para todas las variantes.')
                            ->afterHeader([
                                Actions::make([
                                    Action::make('applyCostToVariants')
                                        ->label('Aplicar')
                                        ->action(fn($livewire) => $livewire->copyCostAndPricesToVariants()),
                                ])->verticallyAlignEnd(true),
                            ])
                            ->schema([
                                TextInput::make('variant_defaults.cost')
                                    ->label('Costo')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('$'),
                                Fieldset::make()
                                    ->label('Listas de precios')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 2,
                                    ])
                                    ->schema([...$priceListFields])
                            ]),
                        Section::make('Stock')
                            ->icon('heroicon-o-archive-box')
                            ->description('Establece el stock para todas las variantes.')
                            ->afterHeader([
                                Actions::make([
                                    Action::make('applyMinStockToVariants')
                                        ->label('Aplicar')
                                        ->action(fn($livewire) => $livewire->copyBranchStocksAndMinStockToVariants()),
                                ])->verticallyAlignEnd(true),
                            ])
                            ->schema([
                                TextInput::make('variant_defaults.min_stock')
                                    ->label('Stock mínimo')
                                    ->numeric()
                                    ->default(0),
                                Fieldset::make()
                                    ->label('Sucursales')
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl' => 2,
                                    ])
                                    ->schema([...$branchStockFields])
                            ])
                    ]),
            ])
            ->collapsed(true);
    }
}
