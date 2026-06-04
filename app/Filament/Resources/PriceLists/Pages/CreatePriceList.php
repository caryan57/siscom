<?php

namespace App\Filament\Resources\PriceLists\Pages;

use App\Filament\Resources\PriceLists\PriceListResource;
use App\Services\CodeGeneratorService;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreatePriceList extends CreateRecord
{
    protected static string $resource = PriceListResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate code from name
        $data['code'] = app(CodeGeneratorService::class)->fromName($data['name']);
        
        return $data;
    }
}
