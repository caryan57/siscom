<?php

namespace App\Filament\Resources\Units\Pages;

use App\Filament\Resources\Units\UnitResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\CodeGeneratorService;
use Override;

class CreateUnit extends CreateRecord
{
    protected static string $resource = UnitResource::class;
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
