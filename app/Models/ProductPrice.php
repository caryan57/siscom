<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use Tenantable;

    protected $table = 'product_prices';
    protected $fillable = [
        'company_id',
        'product_variant_id',
        'price_list_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }
}
