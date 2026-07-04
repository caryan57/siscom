<?php

namespace App\Filament\Resources\Products\Concerns;

use App\Models\Attribute;

trait ConfiguresProductVariantAttributes
{
    public function addVariantAttribute(): void
    {
        $attributeId = data_get(
            $this->data,
            'variant_attribute_form.attribute_id'
        );

        $valuesInput = data_get(
            $this->data,
            'variant_attribute_form.values_input'
        );

        if (!$attributeId || blank($valuesInput)) return;


        $attribute = Attribute::findOrFail($attributeId);

        if (! $attribute) {
            return;
        }

        $values = collect(explode(',', $valuesInput))
            ->map(fn(string $value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (blank($values)) {
            return;
        }

        $variantAttributes = $this->data['variant_attributes'] ?? [];

        $existingIndex = collect($variantAttributes)
            ->search(
                fn($item) =>
                $item['attribute_id'] === $attribute->id
            );

        if ($existingIndex !== false) {

            $existingValues =
                $variantAttributes[$existingIndex]['values'];

            $variantAttributes[$existingIndex]['values']
                = collect([
                    ...$existingValues,
                    ...$values,
                ])
                ->map(fn($value) => trim($value))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } else {

            $variantAttributes[] = [
                'attribute_id' => $attribute->id,
                'attribute_name' => $attribute->name,
                'values' => $values,
            ];
        }

        $this->data['variant_attributes'] = array_values($variantAttributes);
        $this->data['variant_attribute_form'] = ['attribute_id' => null, 'values_input' => null,];

        $this->refreshGeneratedVariants();
    }

    public function addVariantAttributeValue(int $attributeIndex): void
    {
        $variantAttributes = $this->data['variant_attributes'] ?? [];
        $input = $this->data['variant_attribute_inputs'][$attributeIndex] ?? null;

        if (
            blank($input) ||
            ! isset($variantAttributes[$attributeIndex])
        ) {
            return;
        }

        $values = collect(explode(',', $input))
            ->map(fn(string $value) => trim($value))
            ->filter()
            ->unique()
            ->values();

        foreach ($values as $value) {

            $alreadyExists = collect(
                $variantAttributes[$attributeIndex]['values']
            )->contains(
                fn($existing) =>
                str($existing)->lower()->value()
                    === str($value)->lower()->value()
            );

            if ($alreadyExists) {
                continue;
            }

            $variantAttributes[$attributeIndex]['values'][] = $value;
        }

        $variantAttributes[$attributeIndex]['values'] = collect(
            $variantAttributes[$attributeIndex]['values']
        )
            ->unique()
            ->values()
            ->toArray();

        $this->data['variant_attributes'] = $variantAttributes;
        $this->data['variant_attribute_inputs'][$attributeIndex] = null;

        $this->refreshGeneratedVariants();
    }

    public function removeVariantAttribute(int $index): void
    {
        $variantAttributes = $this->data['variant_attributes'] ?? [];
        unset($variantAttributes[$index]);

        $this->data['variant_attributes'] = array_values($variantAttributes);

        $this->refreshGeneratedVariants();
    }

    public function removeVariantAttributeValue(int $attributeIndex, int $valueIndex): void
    {
        $variantAttributes = $this->data['variant_attributes'] ?? [];
        if (!isset($variantAttributes[$attributeIndex])) return;

        unset(
            $variantAttributes[$attributeIndex]['values'][$valueIndex]
        );
        $variantAttributes[$attributeIndex]['values'] = array_values(
            $variantAttributes[$attributeIndex]['values']
        );
        if (
            blank($variantAttributes[$attributeIndex]['values'])
        ) {
            unset($variantAttributes[$attributeIndex]);

            $variantAttributes = array_values(
                $variantAttributes
            );
        }

        $this->data['variant_attributes'] = $variantAttributes;

        $this->refreshGeneratedVariants();
    }

    protected function cartesianProduct(array $arrays): array
    {
        $result = [[]];

        foreach ($arrays as $propertyValues) {
            $tmp = [];
            foreach ($result as $resultItem) {
                foreach ($propertyValues as $value) {
                    $tmp[] = array_merge(
                        $resultItem,
                        [$value]
                    );
                }
            }

            $result = $tmp;
        }
        return $result;
    }
    protected function refreshGeneratedVariants(): void
    {
        $this->initializeVariantDefaults();
        $attributes = $this->data['variant_attributes'] ?? [];

        if (blank($attributes)) {
            $this->data['generated_variants'] = [];

            return;
        }

        $attributeOptions = collect($attributes)
            ->map(fn ($attribute) => collect($attribute['values'])
                ->map(fn ($value) => [
                    'attribute_id' => $attribute['attribute_id'],
                    'attribute_name' => $attribute['attribute_name'],
                    'value' => $value,
                ])
                ->toArray()
            )
            ->toArray();

        $combinations = $this->cartesianProduct($attributeOptions);

        // Usamos una clave estable basada en la combinación
        $existingVariants = collect(
            $this->data['generated_variants'] ?? []
        )->keyBy('key');

        $generatedVariants = [];

        foreach ($combinations as $combination) {

            $mapped = collect($combination)
                ->map(fn ($item) => [
                    'attribute_id' => $item['attribute_id'],
                    'attribute_name' => $item['attribute_name'],
                    'value' => $item['value'],
                ])
                ->values()
                ->toArray();

            $combinationValues = collect($combination)->pluck('value');
            $variantKey = collect($combination)
                ->map(fn ($item) => $item['attribute_id'] . ':' . $item['value'])
                ->implode('|');

            $productName = trim((string) data_get($this->data, 'name'));

            $generatedName = blank($productName)
                ? $combinationValues->implode(' / ')
                : $productName . ' - ' . $combinationValues->implode(' - ');

            $generatedSku = str($productName)
                ->upper()
                ->replace(' ', '-')
                ->append(blank($productName) ? '' : '-')
                ->append(
                    $combinationValues
                        ->map(fn ($value) => str($value)->upper()->replace(' ', '-')->value())
                        ->implode('-')
                )
                ->value();

            $existing = $existingVariants->get($variantKey);
            $generatedVariants[] = [
                'key' => $variantKey,
                'name' => $existing['name'] ?? $generatedName,
                'combination' => $mapped,
                'sku' => $existing['sku'] ?? $generatedSku,
                'barcode' => $existing['barcode'] ?? null,
                'cost' => $existing['cost'] ?? 0,
                'branch_stocks' => $existing['branch_stocks'] ?? array_values($this->data['branch_stocks'] ?? []),
                'prices' => $existing['prices'] ?? [],
            ];
        }

        $this->data['generated_variants'] = $generatedVariants;
    }

    protected function normalizeVariantAttributes(array $attributes): array
    {
        return collect($attributes)
            ->map(function ($attribute) {
                return [
                    'attribute_id' => $attribute['attribute_id'],
                    'values' => collect($attribute['values'])
                        ->map(fn($value) => trim($value))
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray(),
                ];
            })
            ->filter(fn($attribute) => filled($attribute['values']))
            ->values()
            ->toArray();
    }

    protected function initializeVariantDefaults(): void
    {
        $this->data['variant_defaults'] ??= [
            'sku_prefix' => null,
            'cost' => 0,
            'prices' => [],
            'branch_stocks' => [],
            'min_stock' => 0,
        ];
    }

    public function copyCostAndPricesToVariants(): void
    {
        $cost = data_get($this->data, 'variant_defaults.cost');
        $prices = data_get($this->data, 'variant_defaults.prices', []);

        foreach ($this->data['generated_variants'] ?? [] as $index => $variant) {
            if (filled($cost) || $cost === 0 || $cost === '0') {
                $this->data['generated_variants'][$index]['cost'] = $cost;
            }

            foreach ($prices as $priceListId => $price) {
                if (blank($price) && $price !== 0 && $price !== '0') {
                    continue;
                }

                $this->data['generated_variants'][$index]['prices'][$priceListId] = $price;
            }
        }
    }

    public function copyBranchStocksAndMinStockToVariants(): void
    {
        $branchStocks = data_get($this->data, 'variant_defaults.branch_stocks', []);
        $minStock = data_get($this->data, 'variant_defaults.min_stock');

        foreach ($this->data['generated_variants'] ?? [] as $variantIndex => $variant) {
            foreach ($variant['branch_stocks'] ?? [] as $branchStockIndex => $branchStock) {
                $branchId = $branchStock['branch_id'];
                $stock = data_get($branchStocks, "{$branchId}.stock");

                if (filled($stock) || $stock === 0 || $stock === '0') {
                    $this->data['generated_variants'][$variantIndex]['branch_stocks'][$branchStockIndex]['stock'] = $stock;
                }

                if (filled($minStock) || $minStock === 0 || $minStock === '0') {
                    $this->data['generated_variants'][$variantIndex]['branch_stocks'][$branchStockIndex]['min_stock'] = $minStock;
                }
            }
        }
    }
}
