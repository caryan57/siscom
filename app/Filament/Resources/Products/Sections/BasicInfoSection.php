<?php

namespace App\Filament\Resources\Products\Sections;

use App\Enums\Status;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\FileUpload;

use Illuminate\Validation\Rules\Unique;
use Illuminate\Support\Str;

use App\Models\Tax;
use App\Models\Unit;
use Filament\Schemas\Components\Flex;

class BasicInfoSection
{
    public static function make(): Section
    {
        return
            Section::make('Informacion básica')
            ->schema([
                Flex::make([
                    Section::make('General')
                        ->afterHeader([
                            Toggle::make('has_variants')
                                ->label('¿Tiene variantes?')
                                ->live()
                                ->disabled(fn(Get $get) => Unit::isService($get('unit_id')))
                                ->default(false)
                                ->dehydrated(),
                        ])
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre del Producto')
                                ->placeholder('Playera Polo')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                    if (blank($get('variant_name'))) {
                                        $set('variant_name', $state);
                                    }
                                })
                                ->unique(
                                    table: 'products',
                                    column: 'name',
                                    ignoreRecord: true,
                                    modifyRuleUsing: fn(Unique $rule): Unique => $rule->where('company_id', current_company_id()),
                                )
                                ->validationMessages([
                                    'unique' => 'Ya existe un producto con este nombre. Usa otro nombre para continuar.',
                                ])
                                ->required(),
                            Grid::make([
                                'default' => 1,
                                'md' => 2,
                            ])
                                ->schema([
                                    TextInput::make('barcode')
                                        ->label('Código de barras')
                                        ->placeholder('Escanea, escribe o genera')
                                        ->suffixAction(
                                            Action::make('generateCode')
                                                ->icon('heroicon-m-qr-code')
                                                ->tooltip('Generar código')
                                                ->action(function (Set $set): void {
                                                    $number = '';
                                                    for ($i = 0; $i < 13; $i++) {
                                                        $number .= mt_rand(0, 9);
                                                    }
                                                    $set('barcode', $number);
                                                })
                                        )
                                        ->hidden(fn(Get $get): bool => $get('has_variants')),
                                    TextInput::make('sku')
                                        ->label('SKU')
                                        ->placeholder('PPQ-001')
                                        ->maxLength(255),
                                ]),
                            Grid::make([
                                'default' => 1,
                                'md' => 2,
                            ])
                                ->schema([
                                    Select::make('unit_id')
                                        ->label('Unidad de venta')
                                        ->relationship('unit', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->default(fn() => Unit::where('is_default', true)->first()?->id)
                                        ->live()
                                        ->required()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if(Unit::isService($state)){
                                                $set('track_inventory', false);
                                                $set('track_batches', false);
                                                $set('track_expiration', false);
                                                $set('has_variants', false);
                                            }
                                        }),
                                    Select::make('status')
                                        ->label('Estado de venta')
                                        ->options(Status::class)
                                        ->default(Status::Active->value)
                                        ->required(),
                                ]),
                            Textarea::make('description')
                                ->label('Descripción')
                                ->rows(3),
                        ]),
                    Section::make('Adicionales')
                        ->schema([
                            Grid::make([
                                'default' => 1,
                                'md' => 2,
                            ])
                                ->schema([
                                    Select::make('category_id')
                                        ->label('Categoría')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Nombre de la categoría')
                                                ->unique(
                                                    table: 'categories',
                                                    ignoreRecord: true,
                                                    modifyRuleUsing: fn(Unique $rule): Unique => $rule->where('company_id', current_company_id()),
                                                )
                                                ->validationMessages([
                                                    'unique' => 'Ya existe una categoría con este nombre.',
                                                ])
                                                ->required(),
                                        ]),
                                    Select::make('brand_id')
                                        ->label('Marca')
                                        ->relationship('brand', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Nombre de la marca')
                                                ->unique(
                                                    table: 'brands',
                                                    ignoreRecord: true,
                                                    modifyRuleUsing: fn(Unique $rule): Unique => $rule->where('company_id', current_company_id()),
                                                )
                                                ->validationMessages([
                                                    'unique' => 'Ya existe una marca con este nombre.',
                                                ])
                                                ->required(),
                                        ]),
                                ]),
                            Grid::make(1)
                                ->schema([
                                    Select::make('tax_id')
                                        ->label('Impuesto')
                                        ->relationship('tax', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->default(fn() => Tax::where('is_default', true)->first()?->id)
                                        ->required(),
                                ]),
                            FileUpload::make('image')
                                ->label('Imagen del producto')
                                ->image()
                                ->directory('products')
                                ->visibility('public')
                                ->imageEditor()
                                ->getUploadedFileNameForStorageUsing(
                                    fn($file): string => (string) str(Str::random(10) . '-' . $file->getClientOriginalName())
                                )
                                ->maxSize(1024)
                                ->hidden(fn(Get $get): bool => $get('has_variants')),
                        ]),
                ])->from('md'),
            ])
            ->columns(1)
            ->columnSpan(12);
    }
}
