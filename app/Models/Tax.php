<?php

namespace App\Models;

use App\Models\Traits\HasCompanyDefault;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasCompanyDefault;
    use Tenantable;

    protected $table = 'taxes';
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'rate',
        'is_system',
        'is_default',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected function isUsedForCompanyDefaultDeletion(): bool
    {
        return $this->products()->exists();
    }

    protected function companyDefaultDeletionMessage(): string
    {
        return 'No puedes eliminar el impuesto predeterminado.';
    }

    protected function companyDefaultInUseDeletionMessage(): string
    {
        return 'No puedes eliminar este impuesto porque está siendo usado en productos.';
    }
}
