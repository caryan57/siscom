<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Settings = 'settings';
    case Sales = 'sales';

    public function getLabel(): string
    {
        return match ($this) {
            self::Settings => 'Configuración',
            self::Sales => 'Ventas',
        };
    }
}
