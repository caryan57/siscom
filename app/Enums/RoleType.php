<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum RoleType: string implements HasLabel
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case CASHIER = 'cashier';

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::OWNER => 'Propietario',
            self::ADMIN => 'Administrador',
            self::CASHIER => 'Cajero'
        };
    }

    public function toArray(): array
    {
        return [
            'name' => $this->value,
        ];
    }
}
