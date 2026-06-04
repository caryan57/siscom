<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Traits\HasUuid;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasUuid, Tenantable;

    protected $table = 'product_variants';

    protected $fillable = [
        'uuid',
        'company_id',
        'product_id',
        'name',
        'slug',
        'barcode',
        'sku',
        'cost',
        'status',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'status' => Status::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function defaultPrice()
    {
        return $this->hasOne(ProductPrice::class)
            ->whereHas('priceList', fn ($query) => $query->where('is_default', true));
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_values'
        )->withTimestamps();
    }
}
