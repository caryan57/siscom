<?php

namespace App\Actions;

use App\Data\ProductDto;
use App\Enums\InventoryMovementType;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\BranchStock;
use App\Models\ProductLocation;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateProductAction
{
    public function execute(ProductDto $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $this->validateTenantReferences($dto);

            // 1. Crear el producto base
            $product = Product::create([
                'company_id' => $dto->company_id,
                'unit_id' => $dto->unit_id,
                'category_id' => $dto->category_id,
                'brand_id' => $dto->brand_id,
                'tax_id' => $dto->tax_id,
                'name' => $dto->name,
                'description' => $dto->description,
                'image' => $dto->image,
                'track_inventory' => $dto->track_inventory,
                'track_batches' => $dto->track_batches,
                'track_expiration' => $dto->track_expiration,
                'has_variants' => $dto->has_variants,
            ]);

            // 2. Producto con variantes
            if ($dto->has_variants && blank($dto->generated_variants)) throw new \RuntimeException('Debes generar una variante antes de guardar el producto');

            if ($dto->has_variants && filled($dto->generated_variants)) {
                $attributesMap = Attribute::query()
                    ->where('company_id', $dto->company_id)
                    ->get()
                    ->keyBy('id');

                foreach ($dto->generated_variants as $variantData) {
                    $variant = $product->variants()->create([
                        'company_id' => $dto->company_id,
                        'name' => $variantData['name'],
                        'slug' => Str::slug($variantData['name']),
                        'sku' => $variantData['sku'] ?? null,
                        'barcode' => $variantData['barcode'] ?? null,
                        'cost' => filled($variantData['cost'] ?? null)
                            ? (float)$variantData['cost']
                            : null,
                        'status' => $dto->status,
                    ]);

                    $attributeValueIds = [];
                    $combination = $variantData['combination'] ?? [];

                    foreach ($combination as $item) {
                        $attributeId = (int)($item['attribute_id'] ?? 0);
                        $value = trim((string)($item['value'] ?? ''));

                        if (blank($attributeId) || blank($value)) {
                            continue;
                        }

                        if (!$attributesMap->has($attributeId)) throw new \RuntimeException("El atributo con ID '{$attributeId}' ({$item['attribute_name']}) no existe o no pertenece a esta empresa.");

                        $attributeValue = AttributeValue::firstOrCreate([
                            'attribute_id' => $attributeId,
                            'value' => $value,
                        ]);

                        $attributeValueIds[] = $attributeValue->id;
                    }

                    $attributeValueIds = array_values(array_unique($attributeValueIds));

                    if (filled($attributeValueIds)) {
                        $variant->attributeValues()->sync($attributeValueIds);
                    }

                    // Precios: usa los de la variante, cae a los globales si vacíos
                    $variantPrices = filled($variantData['prices'] ?? [])
                        ? $variantData['prices']
                        : $dto->prices;

                    foreach ($variantPrices as $priceListId => $price) {
                        if (blank($price)) continue;
                        $variant->prices()->create([
                            'company_id' => $dto->company_id,
                            'price_list_id' => $priceListId,
                            'price' => (float)$price,
                        ]);
                    }

                    // Stock por sucursal — lee branch_stocks DE LA VARIANTE
                    if ($dto->track_inventory) {
                        foreach ($variantData['branch_stocks'] ?? [] as $branchStock) {
                            $stock = (float)($branchStock['stock'] ?? 0);
                            $minStock = (float)($branchStock['min_stock'] ?? 0);

                            BranchStock::create([
                                'company_id' => $dto->company_id,
                                'branch_id' => $branchStock['branch_id'],
                                'product_variant_id' => $variant->id,
                                'stock' => $stock,
                                'min_stock' => $minStock,
                            ]);

                            if ($stock > 0) {
                                InventoryMovement::create([
                                    'company_id' => $dto->company_id,
                                    'branch_id' => $branchStock['branch_id'],
                                    'product_variant_id' => $variant->id,
                                    'user_id' => auth()->id(),
                                    'quantity' => $stock,
                                    'stock_before' => 0,
                                    'stock_after' => $stock,
                                    'type' => InventoryMovementType::InitialStock,
                                    'notes' => 'Stock inicial al crear el producto'
                                ]);
                            }

                            if (filled($branchStock['location'] ?? null)) {
                                ProductLocation::create([
                                    'company_id' => $dto->company_id,
                                    'branch_id' => $branchStock['branch_id'],
                                    'product_variant_id' => $variant->id,
                                    'location' => $branchStock['location'],
                                ]);
                            }
                        }
                    }
                }

                // 3. Producto simple
            } else {
                $variant = $product->variants()->create([
                    'company_id' => $dto->company_id,
                    'name' => $dto->name,
                    'slug' => Str::slug($dto->name),
                    'sku' => $dto->sku ?? null,
                    'barcode' => $dto->barcode ?? null,
                    'cost' => $dto->cost,
                    'status' => $dto->status,
                ]);

                foreach ($dto->prices as $priceListId => $price) {
                    if (blank($price)) continue;
                    $variant->prices()->create([
                        'company_id' => $dto->company_id,
                        'price_list_id' => $priceListId,
                        'price' => (float)$price,
                    ]);
                }

                if ($dto->track_inventory) {
                    foreach ($dto->branch_stocks as $branchStock) {
                        $stock = (float)($branchStock['stock'] ?? 0);
                        $minStock = (float)($branchStock['min_stock'] ?? 0);

                        BranchStock::create([
                            'company_id' => $dto->company_id,
                            'branch_id' => $branchStock['branch_id'],
                            'product_variant_id' => $variant->id,
                            'stock' => $stock,
                            'min_stock' => $minStock,
                        ]);

                        if ($stock > 0) {
                            InventoryMovement::create([
                                'company_id' => $dto->company_id,
                                'branch_id' => $branchStock['branch_id'],
                                'product_variant_id' => $variant->id,
                                'user_id' => auth()->id(),
                                'quantity' => $stock,
                                'stock_before' => 0,
                                'stock_after' => $stock,
                                'type' => InventoryMovementType::InitialStock,
                                'notes' => 'Stock inicial al crear el producto'
                            ]);
                        }

                        if (filled($branchStock['location'] ?? null)) {
                            ProductLocation::create([
                                'company_id' => $dto->company_id,
                                'branch_id' => $branchStock['branch_id'],
                                'product_variant_id' => $variant->id,
                                'location' => $branchStock['location'],
                            ]);
                        }
                    }
                }
            }

            return $product;
        });
    }

    private function validateTenantReferences(ProductDto $dto): void
    {
        $references = [
            [
                'field' => 'unit_id',
                'model' => Unit::class,
                'id' => $dto->unit_id,
                'message' => 'La unidad seleccionada no pertenece a la empresa activa.',
            ],
            [
                'field' => 'category_id',
                'model' => Category::class,
                'id' => $dto->category_id,
                'message' => 'La categoría seleccionada no pertenece a la empresa activa.',
            ],
            [
                'field' => 'brand_id',
                'model' => Brand::class,
                'id' => $dto->brand_id,
                'message' => 'La marca seleccionada no pertenece a la empresa activa.',
            ],
            [
                'field' => 'tax_id',
                'model' => Tax::class,
                'id' => $dto->tax_id,
                'message' => 'El impuesto seleccionado no pertenece a la empresa activa.',
            ],
        ];

        foreach ($references as $reference) {
            if (blank($reference['id'])) {
                continue;
            }

            $exists = $reference['model']::query()
                ->where('company_id', $dto->company_id)
                ->whereKey($reference['id'])
                ->exists();

            if (!$exists) {
                throw ValidationException::withMessages([
                    $reference['field'] => $reference['message'],
                ]);
            }
        }

        $priceListIds = collect($dto->prices)
            ->filter(fn($price) => filled($price))
            ->keys()
            ->merge(
                collect($dto->generated_variants)
                    ->flatMap(
                        fn($variant) => collect($variant['prices'] ?? [])
                            ->filter(fn($price) => filled($price))
                            ->keys()
                    )
            )
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values();

        $validPriceListIds = PriceList::query()
            ->where('company_id', $dto->company_id)
            ->whereKey($priceListIds->all())
            ->pluck('id');

        if (filled($priceListIds->diff($validPriceListIds))) {
            throw ValidationException::withMessages([
                'prices' => 'Una de las listas de precios no pertenece a la empresa activa.',
            ]);
        }

        if (!$dto->track_inventory) {
            return;
        }

        $branchIds = collect($dto->branch_stocks)
            ->pluck('branch_id')
            ->merge(
                collect($dto->generated_variants)
                    ->flatMap(
                        fn($variant) => collect($variant['branch_stocks'] ?? [])
                            ->pluck('branch_id')
                    )
            )
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values();

        $validBranchIds = Branch::query()
            ->where('company_id', $dto->company_id)
            ->whereKey($branchIds->all())
            ->pluck('id');

        if (filled($branchIds->diff($validBranchIds))) {
            throw ValidationException::withMessages([
                'branch_stocks' => 'Una de las sucursales no pertenece a la empresa activa.',
            ]);
        }
    }
}
