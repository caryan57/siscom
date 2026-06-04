<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum TaxType: string implements HasLabel
{
    case IVA_16 = 'iva_16';
    case IVA_8 = 'iva_8';
    case EXEMPT = 'exempt';

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::IVA_16 => 'IVA 16%',
            self::IVA_8 => 'IVA 8%',
            self::EXEMPT => 'Exento',
        };
    }

    public function getRate(): float
    {
        return match ($this) {
            self::IVA_16 => 16.00,
            self::IVA_8 => 8.00,
            self::EXEMPT => 0.00,
        };
    }

    public function isDefault(): bool
    {
        return $this === self::IVA_16;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getLabel(),
            'code' => $this->value,
            'rate' => $this->getRate(),
            'is_default' => $this->isDefault(),
            'is_system' => true,
            'status' => true,
        ];
    }
}
