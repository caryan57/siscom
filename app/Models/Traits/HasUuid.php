<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUlids;

trait HasUuid
{
    use HasUlids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
