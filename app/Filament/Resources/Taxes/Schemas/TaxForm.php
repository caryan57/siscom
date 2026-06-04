<?php

namespace App\Filament\Resources\Taxes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class TaxForm
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
                        'unique' => 'Ya existe un impuesto con este nombre. Usa otro nombre para continuar.',
                    ])
                    ->required(),
                TextInput::make('rate')
                    ->label('Tasa')
                    ->required()
                    ->numeric(),
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
