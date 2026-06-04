<?php

namespace App\Models;

use App\Models\Traits\HasCompanyDefault;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasCompanyDefault;
    use Tenantable;

    protected $table = 'units';
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'is_system',
        'is_default',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function isService(?int $unitId): bool
    {
        if (blank($unitId)) {
            return false;
        }

        return static::query()
            ->whereKey($unitId)
            ->where('code', 'service')
            ->exists();
    }

    protected function companyDefaultDeletionMessage(): string
    {
        return 'No puedes eliminar la unidad predeterminada.';
    }

    protected function isUsedForCompanyDefaultDeletion(): bool
    {
        return $this->products()->exists();
    }

    protected function companyDefaultInUseDeletionMessage(): string
    {
        return 'No puedes eliminar esta unidad porque está siendo usada en productos.';
    }
}
