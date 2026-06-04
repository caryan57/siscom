<?php

namespace App\Models;

use App\Models\Traits\HasCompanyDefault;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasCompanyDefault;
    use Tenantable;

    protected $table = 'price_lists';
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'is_system',
        'is_default',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    protected function isUsedForCompanyDefaultDeletion(): bool
    {
        return $this->productPrices()->exists();
    }

    protected function companyDefaultDeletionMessage(): string
    {
        return 'No puedes eliminar la lista de precios predeterminada.';
    }

    protected function companyDefaultInUseDeletionMessage(): string
    {
        return 'No puedes eliminar esta lista de precios porque está siendo usada en productos.';
    }
}
