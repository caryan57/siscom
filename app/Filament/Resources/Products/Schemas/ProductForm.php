<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Resources\Products\Sections\BasicInfoSection;
use App\Filament\Resources\Products\Sections\InventorySection;
use App\Filament\Resources\Products\Sections\PricingSection;
use App\Filament\Resources\Products\Sections\VariantsSection;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                BasicInfoSection::make(),
                PricingSection::make(),
                VariantsSection::make(),
                InventorySection::make(),
            ]);
    }
}
