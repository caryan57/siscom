<?php

namespace App\Data;

use App\Models\Unit;

class ProductDto
{
    public function __construct(
        public int $company_id,
        public string $name,
        public int $unit_id,
        public ?int $category_id,
        public ?int $brand_id,
        public ?int $tax_id,
        public ?string $variant_name,
        public ?string $sku,
        public ?string $barcode,
        public ?string $description,
        public ?string $image,
        public ?float $cost,
        public bool $status,
        public bool $has_variants,
        public bool $track_inventory,
        public bool $track_batches,
        public bool $track_expiration,
        public array $prices,
        public array $generated_variants,
        public array $branch_stocks,
    ) {}

    public static function from(array $data): self
    {
        // Determine if the product is a service based on the unit_id, so we can set appropriate defaults for service products
        $isService = Unit::isService((int) $data['unit_id']);

        $categoryId = $data['category_id'] ?? null;
        $brandId = $data['brand_id'] ?? null;
        $taxId = $data['tax_id'] ?? null;
        $cost = $data['cost'] ?? null;
        $trackInventory = !$isService && (bool)($data['track_inventory'] ?? true);
        $trackBatches = $trackInventory && (bool) ($data['track_batches'] ?? false);
        $trackExpiration = $trackBatches && (bool) ($data['track_expiration'] ?? false);

        return new self(
            company_id: current_company_id(),
            name: $data['name'],
            unit_id: (int) $data['unit_id'],
            category_id: filled($categoryId) ? (int) $categoryId : null,
            brand_id: filled($brandId) ? (int) $brandId : null,
            tax_id: filled($taxId) ? (int) $taxId : null,
            variant_name: $data['variant_name'] ?? null,
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            description: $data['description'] ?? null,
            image: $data['image'] ?? null,
            cost: filled($cost) ? (float) $cost : null,
            status: (bool) ($data['status'] ?? true),
            has_variants: !$isService && (bool)($data['has_variants'] ?? false),
            track_inventory: $trackInventory,
            track_batches: $trackBatches,
            track_expiration: $trackExpiration,
            prices: $data['prices'] ?? [],
            generated_variants: $data['generated_variants'] ?? [],
            branch_stocks: $data['branch_stocks'] ?? [],
        );
    }
}
