<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nombre de Sucursal'),
                TextEntry::make('address')
                    ->label('Dirección de Sucursal')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email de Sucursal')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->label('Telefono de Sucursal')
                    ->placeholder('-'),
                IconEntry::make('is_default')
                    ->label('Predeterminado')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
