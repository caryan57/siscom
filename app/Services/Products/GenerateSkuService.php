<?php

namespace App\Services\Products;

class GenerateSkuService
{
  public function generate(string $name): string
    {
        $prefix = collect(explode(' ', $name))
            ->map(fn ($word) => strtoupper(substr($word, 0, 3)))
            ->implode('');

        return $prefix . '-' . random_int(1000, 9999);
    }
}