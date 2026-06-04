<?php

namespace App\Actions;

use App\Data\ProductDto;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\ProductLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function execute(ProductDto $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            // 1. Crear el producto base
            $product = Product::create([
                'company_id'       => $dto->company_id,
                'unit_id'          => $dto->unit_id,
                'category_id'      => $dto->category_id,
                'brand_id'         => $dto->brand_id,
                'tax_id'           => $dto->tax_id,
                'name'             => $dto->name,
                'description'      => $dto->description,
                'image'            => $dto->image,
                'track_inventory'  => $dto->track_inventory,
                'track_batches'    => $dto->track_batches,
                'track_expiration' => $dto->track_expiration,
                'has_variants'     => $dto->has_variants,
            ]);

            // 2. Producto con variantes
            if ($dto->has_variants && filled($dto->generated_variants)) {

                foreach ($dto->generated_variants as $variantData) {
                    $variant = $product->variants()->create([
                        'company_id' => $dto->company_id,
                        'name'       => $variantData['name'],
                        'slug'       => Str::slug($variantData['name']),
                        'sku'        => $variantData['sku'] ?? null,
                        'barcode'    => $variantData['barcode'] ?? null,
                        'cost'       => filled($variantData['cost'] ?? null)
                                            ? (float) $variantData['cost']
                                            : null,
                    ]);

                    // Precios: usa los de la variante, cae a los globales si vacíos
                    $variantPrices = filled($variantData['prices'] ?? [])
                        ? $variantData['prices']
                        : $dto->prices;

                    foreach ($variantPrices as $priceListId => $price) {
                        if (blank($price)) continue;
                        $variant->prices()->create([
                            'company_id'    => $dto->company_id,
                            'price_list_id' => $priceListId,
                            'price'         => (float) $price,
                        ]);
                    }

                    // Stock por sucursal — lee branch_stocks DE LA VARIANTE
                    if ($dto->track_inventory) {
                        foreach ($variantData['branch_stocks'] ?? [] as $branchStock) {
                            BranchStock::create([
                                'company_id'         => $dto->company_id,
                                'branch_id'          => $branchStock['branch_id'],
                                'product_variant_id' => $variant->id,
                                'stock'              => (float) ($branchStock['stock'] ?? 0),
                                'min_stock'          => (float) ($branchStock['min_stock'] ?? 0),
                            ]);

                            if (filled($branchStock['location'] ?? null)) {
                                ProductLocation::create([
                                    'company_id'         => $dto->company_id,
                                    'branch_id'          => $branchStock['branch_id'],
                                    'product_variant_id' => $variant->id,
                                    'location'           => $branchStock['location'],
                                ]);
                            }
                        }
                    }
                }

            // 3. Producto simple
            } else {
                $variant = $product->variants()->create([
                    'company_id' => $dto->company_id,
                    'name'       => $dto->name,
                    'slug'       => Str::slug($dto->name),
                    'sku'        => $dto->sku ?? null,
                    'barcode'    => $dto->barcode ?? null,
                    'cost'       => $dto->cost,
                ]);

                foreach ($dto->prices as $priceListId => $price) {
                    if (blank($price)) continue;
                    $variant->prices()->create([
                        'company_id'    => $dto->company_id,
                        'price_list_id' => $priceListId,
                        'price'         => (float) $price,
                    ]);
                }

                if ($dto->track_inventory) {
                    foreach ($dto->branch_stocks as $branchStock) {
                        BranchStock::create([
                            'company_id'         => $dto->company_id,
                            'branch_id'          => $branchStock['branch_id'],
                            'product_variant_id' => $variant->id,
                            'stock'              => (float) ($branchStock['stock'] ?? 0),
                            'min_stock'          => (float) ($branchStock['min_stock'] ?? 0),
                        ]);

                        if (filled($branchStock['location'] ?? null)) {
                            ProductLocation::create([
                                'company_id'         => $dto->company_id,
                                'branch_id'          => $branchStock['branch_id'],
                                'product_variant_id' => $variant->id,
                                'location'           => $branchStock['location'],
                            ]);
                        }
                    }
                }
            }

            return $product;
        });
    }
}