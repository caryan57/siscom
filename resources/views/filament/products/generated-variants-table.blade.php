@php
    $variants = $generatedVariants ?? [];
    $attributes = $attributes ?? [];
    $priceLists = $priceLists ?? collect();
    $branches = $branches ?? collect();

    $inputClass = '
    w-full
    rounded-lg
    border
    border-gray-300
    px-2
    py-1.5
    text-sm
    shadow-sm
    transition
    focus:border-primary-500
    focus:ring-1
    focus:ring-primary-500
    dark:border-gray-600
    dark:bg-gray-800
    dark:text-gray-100
    ';
@endphp

@if (is_array($variants) && !empty($variants))
    <div class="mt-4">
        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 z-10">
                <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">

                    {{-- Atributos --}}
                    @foreach ($attributes as $attribute)
                        <th
                            class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            {{ $attribute['attribute_name'] }}
                        </th>
                    @endforeach

                    <th
                        class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">
                        SKU
                    </th>

                    <th
                        class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">
                        Código de barras
                    </th>
                    <th class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-left font-semibold">
                        Costo
                    </th>

                    @foreach ($priceLists as $priceList)
                        <th
                            class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-left font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">
                            {{ $priceList->name }}

                            @if ($priceList->is_default)
                                <span class="ml-1 text-[10px] font-normal text-primary-500">
                            Base
                        </span>
                            @endif
                        </th>
                    @endforeach
                    <th
                        class="whitespace-nowrap px-3 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                        Inventario
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach (($this->data['generated_variants'] ?? []) as $index => $variant)
                    @php
                        $combinationByAttributeId = collect($variant['combination'] ?? [])
                            ->keyBy(fn ($item) => (int) data_get($item, 'attribute_id'));
                    @endphp
                    <tr wire:key="variant-row-{{ $index }}"
                        class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50">

                        {{-- Valores de atributos --}}
                        @foreach ($attributes as $attribute)
                            <td
                                class="whitespace-nowrap border-r border-gray-200 px-3 py-3 text-gray-600 dark:border-gray-700 dark:text-gray-400">
                                {{ data_get($combinationByAttributeId->get((int) $attribute['attribute_id']),'value','-') }}
                            </td>
                        @endforeach

                        {{-- SKU --}}
                        <td class="border-r border-gray-200 px-2 py-2 dark:border-gray-700">
                            <input type="text" wire:model.defer="data.generated_variants.{{ $index }}.sku"
                                   placeholder="SKU-{{ $index + 1 }}" class="{{ $inputClass }} min-w-[120px]"/>
                        </td>

                        {{-- Código de barras --}}
                        <td class="border-r border-gray-200 px-2 py-2 dark:border-gray-700">
                            <input type="text"
                                   wire:model.defer="data.generated_variants.{{ $index }}.barcode"
                                   placeholder="Código" class="{{ $inputClass }} min-w-[140px]"/>
                        </td>

                        {{-- Costo --}}
                        <td class="border-r border-gray-200 px-2 py-2 dark:border-gray-700">
                            <input type="number" min="0" step="0.01"
                                   wire:model.defer="data.generated_variants.{{ $index }}.cost"
                                   class="{{ $inputClass }} min-w-[100px]"/>
                        </td>

                        {{-- Precios --}}
                        @foreach ($priceLists as $priceList)
                            @php
                                $currentPrice = $variant['prices'][$priceList->id] ?? null;
                            @endphp

                            <td wire:key="variant-price-{{ $index }}-{{ $priceList->id }}"
                                class="border-r border-gray-200 px-2 py-2 dark:border-gray-700">
                                <div class="relative min-w-[110px]">

                            <span
                                class="pointer-events-none absolute left-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                                $
                            </span>
                                    <input type="number"
                                           wire:model.defer="data.generated_variants.{{ $index }}.prices.{{ $priceList->id }}"
                                           x-data="{}"
                                           x-bind:class="$el.value !== '' &&
                                                $el.value != ($wire.get('data.prices.{{ $priceList->id }}') ?? '') ?
                                                'border-warning-500 bg-warning-50 text-warning-800 dark:bg-warning-950' :
                                                'border-gray-300 bg-gray-50 text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                           class="
                                            w-full
                                            rounded-lg
                                            border
                                            pl-5
                                            pr-12
                                            py-1.5
                                            text-sm
                                            shadow-sm
                                            transition
                                            focus:border-primary-500
                                            focus:ring-1
                                            focus:ring-primary-500
                                        "/>
                                </div>
                            </td>
                        @endforeach
                        {{-- Inventario --}}
                        <td class="px-3 py-2">
                            <button type="button"
                                    x-on:click="$dispatch('open-inventory-modal-{{ $index }}')"
                                    class="
                                    rounded-lg
                                    bg-primary-600
                                    px-3
                                    py-2
                                    text-xs
                                    font-medium
                                    text-white
                                    hover:bg-primary-500
                                ">
                                Configurar
                            </button>
                        </td>
                    </tr>
                    <tr x-data="{ open: false }" x-on:open-inventory-modal-{{ $index }}.window="open = true"
                        x-show="open" x-cloak>
                        <td colspan="{{ count($attributes) + count($priceLists) + 5 }}"
                            class="bg-gray-50 dark:bg-gray-900">

                            <div class="p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="font-semibold">
                                        Inventario por sucursal
                                    </h4>
                                    <button type="button" x-on:click="open = false" class="text-sm text-gray-500">
                                        Cerrar
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($variant['branch_stocks'] ?? [] as $branchIndex => $branchStock)
                                        <div
                                            class="
                                                rounded-lg
                                                border
                                                border-gray-200
                                                p-4
                                                dark:border-gray-700
                                            ">
                                            <h5 class="mb-3 font-medium">
                                                {{ $branchStock['branch_name'] }}
                                            </h5>
                                            <div class="grid gap-4 md:grid-cols-3">
                                                <div>
                                                    <label class="mb-1 block text-xs">
                                                        Stock
                                                    </label>
                                                    <input type="number" min="0"
                                                           wire:model.live="data.generated_variants.{{ $index }}.branch_stocks.{{ $branchIndex }}.stock"
                                                           class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs">
                                                        Stock mínimo
                                                    </label>
                                                    <input type="number" min="0"
                                                           wire:model.live="data.generated_variants.{{ $index }}.branch_stocks.{{ $branchIndex }}.min_stock"
                                                           class="{{ $inputClass }}">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs">
                                                        Ubicación
                                                    </label>

                                                    <input type="text"
                                                           wire:model.live="data.generated_variants.{{ $index }}.branch_stocks.{{ $branchIndex }}.location"
                                                           class="{{ $inputClass }}">
                                                </div>

                                            </div>

                                        </div>
                                    @endforeach
                                </div>

                            </div>

                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        {{-- Resumen --}}
        <div class="mt-4 flex flex-wrap items-center gap-4">
            <div
                class="rounded-lg border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-800 dark:bg-warning-950 dark:text-warning-300">
                ⚠️ Puedes modificar cualquier campo antes de guardar.
            </div>
        </div>
@endif
