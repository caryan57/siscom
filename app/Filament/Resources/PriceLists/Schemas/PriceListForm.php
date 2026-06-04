<?php

namespace App\Filament\Resources\PriceLists\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class PriceListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('company_id', current_company_id()),
                    )
                    ->validationMessages([
                        'unique' => 'Ya existe una lista de precios con este nombre. Usa otro nombre para continuar.',
                    ])
                    ->required(),
                Toggle::make('is_default')
                    ->label('Predeterminado')
                    ->helperText('Si desea cambiar el valor predeterminado, marque esta opción en el nuevo elemento.')
                    ->disabled(fn ($record) => $record?->is_default)
                    ->dehydrated()
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),
            ]);
    }
}
