<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum UnitType: string implements HasLabel
{
    case Piece = 'piece';
    case Service = 'service';
    case Box = 'box';
    case Kilogram = 'kilogram';
    case Miligram = 'miligram';
    case Liter = 'liter';
    case Mililiter = 'mililiter';
    case Meter = 'meter';
    case Centimeter = 'centimeter';

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Piece => 'Pieza',
            self::Service => 'Servicio',
            self::Box => 'Caja',
            self::Kilogram => 'Kilogramo',
            self::Miligram => 'Miligramo',
            self::Liter => 'Litro',
            self::Mililiter => 'Mililitro',
            self::Meter => 'Metro',
            self::Centimeter => 'Centímetro',
        };
    }

    public function isDefault(): bool
    {
        return $this === self::Piece;
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
