<?php

namespace App\Models;

use App\Models\Traits\Branchable;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class ProductLocation extends Model
{
    use Tenantable, Branchable;

    protected $fillable = [
        'company_id',
        'branch_id',
        'product_variant_id',
        'location',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
