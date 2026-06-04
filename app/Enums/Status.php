<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Status: int implements HasLabel
{
    case Active = 1;
    case Inactive = 0;

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active   => 'success',
            self::Inactive => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Active   => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-x-circle',
        };
    }
}