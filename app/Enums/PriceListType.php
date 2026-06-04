<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum PriceListType: string implements HasLabel
{
    case GeneralPublic = 'general_public';
    case Wholesale = 'wholesale';

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::GeneralPublic => 'Público General',
            self::Wholesale => 'Mayoreo',
        };
    }

    public function isDefault(): bool
    {
        return $this === self::GeneralPublic;
    }


    public function toArray(): array
    {
        return [
            'name' => $this->getLabel(),
            'code' => $this->value,
            'is_default' => $this->isDefault(),
            'is_system' => true,
            'status' => true,
        ];
    }
}