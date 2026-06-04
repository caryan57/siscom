@php
    $attributes = collect($get('variant_attributes') ?? [])
    ->filter(fn ($attribute) => filled($attribute['values'] ?? []));

    $combinationsCount = $attributes->isEmpty()
        ? 0
        : $attributes
            ->pluck('values')
            ->map(fn ($values) => count($values))
            ->reduce(
                fn ($total, $count) =>
                    $total * $count,
                1
            );
@endphp

@if ($attributes->isNotEmpty())
    <div style="margin-bottom:12px; display:flex; flex-direction:column; gap:8px;">
        @foreach ($attributes as $index => $attribute)
            <div
            wire:key="variant-attribute-{{ $attribute['attribute_id'] }}"
            style=" display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span
                    style=" font-size:13px; font-weight:600; color:#374151; min-width:60px;">
                    {{ $attribute['attribute_name'] }}:
                </span>
                @foreach ($attribute['values'] as $value)
                    <button
                    type="button"
                    wire:key="variant-value-{{ $attribute['attribute_id'] }}-{{ $loop->index }}"
                    wire:click="removeVariantAttributeValue({{ $index }}, {{ $loop->index }})"
                        style="
                            display:flex;
                            align-items:center;
                            gap:6px;
                            background:#f3f4f6;
                            border:none;
                            border-radius:5px;
                            padding:2px 8px;
                            font-size:12px;
                            color:#374151;
                            cursor:pointer;"
                        >
                            <span>{{ $value }}</span>
                            <span style="font-size:11px;">✕</span>
                    </button>
                @endforeach
                <div style=" display:flex; align-items:center; gap:6px; margin-top:4px;">
                        <input
                        type="text"
                        wire:model.defer="data.variant_attribute_inputs.{{ $index }}"
                        placeholder="Agregar valor"
                        style="
                            border:1px solid #e5e7eb;
                            border-radius:6px;
                            padding:4px 8px;
                            font-size:12px;
                            width:140px;
                        "/>
                        <button
                            type="button"
                            wire:key="variant-value-{{ $attribute['attribute_id'] }}-{{ $loop->index }}"
                            wire:click="addVariantAttributeValue({{ $index }})"
                            style="
                                border:none;
                                background:#111827;
                                color:white;
                                border-radius:6px;
                                padding:4px 10px;
                                cursor:pointer;
                                font-size:12px;"
                            >
                            +
                        </button>
                    </div>
                <div style=" margin-left:auto; display:flex; gap:4px;">
                    <button
                        type="button"
                        wire:key="variant-value-{{ $attribute['attribute_id'] }}-{{ $loop->index }}"
                        wire:click="removeVariantAttribute({{ $index }})"
                        style="
                            border:1px solid #fecaca;
                            background:#fff5f5;
                            border-radius:6px;
                            padding:4px 6px;
                            cursor:pointer;
                            color:#ef4444;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                        "
                        title="Eliminar atributo"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            style="width:14px; height:14px;"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 7.5h12m-1.5 0v10.125A2.625 2.625 0 0113.875 20.25h-3.75A2.625 2.625 0 017.5 17.625V7.5m3-3h3a1.5 1.5 0 011.5 1.5v1.5h-6V6a1.5 1.5 0 011.5-1.5z"
                            />
                        </svg>

                    </button>
                </div>
            </div>
        @endforeach
    </div>
    @if($combinationsCount > 0)
        <div style=" margin-top:12px; font-size:13px; color:#6b7280;">
        {{ $combinationsCount }}
        {{ Str::plural('variante ', $combinationsCount) }}posible{{ $combinationsCount > 1 ? 's' : '' }}
    </div>
    @endif
@endif