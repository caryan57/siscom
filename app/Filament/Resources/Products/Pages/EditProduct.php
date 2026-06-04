<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\ConfiguresProductVariantAttributes;
use App\Filament\Resources\Products\ProductResource;
use App\Models\BatchStock;
use App\Models\BranchStock;
use App\Models\ProductLocation;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EditProduct extends EditRecord
{
    use ConfiguresProductVariantAttributes;

    protected static string $resource = ProductResource::class;

    protected array $workflowData = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variant = $this->record->baseVariant;
        $branchStock = $variant
            ? $variant->branchStocks()->where('branch_id', current_branch_id())->first() ?? $variant->branchStocks()->first()
            : null;
        $location = $variant
            ? ProductLocation::withoutGlobalScopes()->where('product_variant_id', $variant->id)->where('branch_id', $branchStock?->branch_id)->first()
            : null;
        $batch = $variant?->batches()->oldest('id')->first();

        $data['variant_name'] = $variant?->name ?? $data['name'];
        $data['sku'] = $variant?->sku;
        $data['barcode'] = $variant?->barcode;
        $data['cost'] = $variant?->cost;
        $data['status'] = $variant?->status?->value ?? true;
        $data['prices'] = $variant?->prices()->pluck('price', 'price_list_id')->toArray() ?? [];
        $data['stock_branch_id'] = $branchStock?->branch_id ?? current_branch_id();
        $data['initial_stock'] = $branchStock?->stock ?? 0;
        $data['min_stock'] = $branchStock?->min_stock ?? 0;
        $data['location'] = $location?->location;
        $data['lot_number'] = $batch?->lot_number;
        $data['manufactured_at'] = $batch?->manufactured_at;
        $data['expires_at'] = $batch?->expires_at;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->workflowData = $this->normalizeWorkflowData($data);

        return Arr::only($this->workflowData, [
            'category_id',
            'brand_id',
            'tax_id',
            'unit_id',
            'name',
            'description',
            'image',
            'track_inventory',
            'track_batches',
            'track_expiration',
            'expiration_alert_days',
            'has_variants',
        ]);
    }

    protected function afterSave(): void
    {
        $data = $this->workflowData;

        $variant = $this->record->variants()->oldest('id')->first();

        $variantData = [
            'company_id' => current_company_id(),
            'name' => $data['variant_name'] ?: $data['name'],
            'slug' => Str::slug($data['variant_name'] ?: $data['name']),
            'sku' => $data['sku'] ?: null,
            'barcode' => $data['barcode'] ?: null,
            'cost' => $data['cost'] ?: null,
            'status' => $data['status'],
        ];

        if ($variant) {
            $variant->update($variantData);
        } else {
            $variant = $this->record->variants()->create($variantData);
        }

        foreach ($data['prices'] as $priceListId => $price) {
            if (blank($price)) {
                $variant->prices()->where('price_list_id', $priceListId)->delete();

                continue;
            }

            $variant->prices()->updateOrCreate(
                ['price_list_id' => $priceListId],
                [
                    'company_id' => current_company_id(),
                    'price' => $price,
                ],
            );
        }

        if (! $data['track_inventory']) {
            BranchStock::withoutGlobalScopes()->where('product_variant_id', $variant->id)->delete();
            $variant->batches()->delete();
            ProductLocation::withoutGlobalScopes()->where('product_variant_id', $variant->id)->delete();

            return;
        }

        BranchStock::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => current_company_id(),
                'branch_id' => $data['stock_branch_id'],
                'product_variant_id' => $variant->id,
            ],
            [
                'stock' => $data['initial_stock'],
                'min_stock' => $data['min_stock'],
            ],
        );

        if (filled($data['location'])) {
            ProductLocation::withoutGlobalScopes()->updateOrCreate(
                [
                    'company_id' => current_company_id(),
                    'branch_id' => $data['stock_branch_id'],
                    'product_variant_id' => $variant->id,
                ],
                [
                    'location' => $data['location'],
                ],
            );
        } else {
            ProductLocation::withoutGlobalScopes()
                ->where('branch_id', $data['stock_branch_id'])
                ->where('product_variant_id', $variant->id)
                ->delete();
        }

        if (! $data['track_batches']) {
            $variant->batches()->delete();

            return;
        }

        $batch = $variant->batches()->updateOrCreate(
            [
                'company_id' => current_company_id(),
                'lot_number' => $data['lot_number'],
            ],
            [
                'manufactured_at' => $data['manufactured_at'],
                'expires_at' => $data['expires_at'],
                'cost' => $data['cost'] ?: null,
                'status' => true,
            ],
        );

        BatchStock::withoutGlobalScopes()->updateOrCreate(
            [
                'company_id' => current_company_id(),
                'branch_id' => $data['stock_branch_id'],
                'batch_id' => $batch->id,
            ],
            [
                'stock' => $data['initial_stock'],
            ],
        );
    }

    private function normalizeWorkflowData(array $data): array
    {
        $data['variant_name'] = $data['variant_name'] ?? $data['name'];
        $data['status'] = $data['status'] ?? true;
        $data['prices'] = $data['prices'] ?? [];
        $data['track_inventory'] = (bool) ($data['track_inventory'] ?? true);
        $data['track_batches'] = $data['track_inventory'] && (bool) ($data['track_batches'] ?? false);
        $data['track_expiration'] = $data['track_batches'] && (bool) ($data['track_expiration'] ?? false);
        $data['expiration_alert_days'] = $data['track_expiration'] ? ($data['expiration_alert_days'] ?? null) : null;
        $data['stock_branch_id'] = $data['stock_branch_id'] ?? current_branch_id();
        $data['initial_stock'] = $data['track_inventory'] ? ($data['initial_stock'] ?? 0) : 0;
        $data['min_stock'] = $data['track_inventory'] ? ($data['min_stock'] ?? 0) : 0;
        $data['location'] = $data['track_inventory'] ? ($data['location'] ?? null) : null;
        $data['lot_number'] = $data['track_batches'] ? ($data['lot_number'] ?? null) : null;
        $data['manufactured_at'] = $data['track_batches'] ? ($data['manufactured_at'] ?? null) : null;
        $data['expires_at'] = $data['track_expiration'] ? ($data['expires_at'] ?? null) : null;
        $data['variant_attributes'] = ($data['has_variants'] ?? false)
            ? ($data['variant_attributes'] ?? [])
            : [];

        return $data;
    }
}
