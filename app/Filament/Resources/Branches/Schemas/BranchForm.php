<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Section::make('Configuracion')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre de Sucursal')
                                    ->placeholder('Ingresar nombre')
                                    ->unique(
                                        table: 'branches',
                                        column: 'name',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('company_id', current_company_id()),
                                    )
                                    ->rule(fn (?Branch $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($record): void {
                                        $exists = Branch::query()
                                            ->where('company_id', current_company_id())
                                            ->where('slug', Str::slug((string) $value))
                                            ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                            ->exists();

                                        if ($exists) {
                                            $fail('Ya existe una sucursal con un nombre igual o muy parecido. Usa otro nombre para continuar.');
                                        }
                                    })
                                    ->validationMessages([
                                        'unique' => 'Ya existe una sucursal con este nombre. Usa otro nombre para continuar.',
                                    ])
                                    ->required(),
                                Toggle::make('is_default')
                                    ->label('Predeterminado')
                                    ->helperText('Si desea cambiar el valor predeterminado, marque esta opción en el nuevo elemento.')
                                    ->disabled(fn ($record) => $record?->is_default)
                                    ->dehydrated()
                                    ->visible(fn (string $operation): bool => $operation === 'edit')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark'),
                            ]),
                        Section::make('Datos de contacto')
                            ->schema([
                                TextInput::make('address')
                                    ->label('Dirección de Sucursal')
                                    ->placeholder('Ingresar dirección completa'),
                                Grid::make()
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('Email de Sucursal')
                                            ->placeholder('Ingresar email')
                                            ->email(),
                                        TextInput::make('phone')
                                            ->label('Teléfono de Sucursal')
                                            ->placeholder('Ingresar teléfono')
                                            ->tel(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
