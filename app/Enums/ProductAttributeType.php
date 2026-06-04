<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Override;

enum ProductAttributeType: string implements HasLabel
{
    case Color = 'color';
    case ClothingSize = 'clothing_size';
    case GeneralSize = 'general_size';
    case Capacity = 'capacity';
    case Flavor = 'flavor';
    case MaterialType = 'material_type';
    case Model = 'model';
    case Style = 'style';

    #[Override]
    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Color => 'Color',
            self::ClothingSize => 'Talla',
            self::GeneralSize => 'Tamaño',
            self::Capacity => 'Capacidad',
            self::Flavor => 'Sabor',
            self::MaterialType => 'Material',
            self::Model => 'Modelo',
            self::Style => 'Estilo',
        };
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getLabel(),
            'code' => $this->value,
            'is_system' => true,
        ];
    }
}