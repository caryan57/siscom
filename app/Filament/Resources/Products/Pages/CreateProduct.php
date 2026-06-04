<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\ConfiguresProductVariantAttributes;
use App\Filament\Resources\Products\ProductResource;
use App\Data\ProductDto;
use App\Actions\CreateProductAction;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateProduct extends CreateRecord
{
    use ConfiguresProductVariantAttributes;

    protected static string $resource = ProductResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    #[Override]
    protected function handleRecordCreation(array $data): Product
    {
        $dto = ProductDto::from($data);

        return app(CreateProductAction::class)->execute($dto);
    }
}

