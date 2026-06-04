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
        
        return new self(
            company_id: current_company_id(),
            name: $data['name'],
            unit_id: (int) $data['unit_id'],
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            brand_id: isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            tax_id: isset($data['tax_id']) ? (int) $data['tax_id'] : null,
            variant_name: $data['variant_name'] ?? null,
            sku: $data['sku'] ?? null,
            barcode: $data['barcode'] ?? null,
            description: $data['description'] ?? null,
            image: $data['image'] ?? null,
            cost: isset($data['cost']) ? (float) $data['cost'] : null,
            status: (bool) ($data['status'] ?? true),
            has_variants: $isService ? false : (bool) ($data['has_variants'] ?? false),
            track_inventory: $isService ? false : (bool) ($data['track_inventory'] ?? true),
            track_batches: $isService ? false : (bool) ($data['track_batches'] ?? false),
            track_expiration: $isService ? false : (bool) ($data['track_expiration'] ?? false),
            prices: $data['prices'] ?? [],
            generated_variants: $data['generated_variants'] ?? [],
            branch_stocks: $data['branch_stocks'] ?? [],
        );
    }
}