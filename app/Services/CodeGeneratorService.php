<?php

namespace App\Services;
use Illuminate\Support\Str;

class CodeGeneratorService
{
    /**
     * Create a new code from name
     */
    public function fromName(string $name): string
    {
        return Str::lower(Str::trim(Str::slug($name, '_')));
    }
}
