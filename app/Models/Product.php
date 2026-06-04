<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasUuid, Tenantable;

    protected $table = 'products';

    protected $fillable = [
        'uuid',
        'company_id',
        'category_id',
        'brand_id',
        'tax_id',
        'unit_id',
        'name',
        'description',
        'image',
        'track_inventory',
        'track_batches',
        'track_expiration',
        'expiration_alert_days',
        'has_variants',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function baseVariant()
    {
        return $this->hasOne(ProductVariant::class)->oldestOfMany();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function prices()
    {
        return $this->hasManyThrough(ProductPrice::class, ProductVariant::class);
    }

    public function branchStocks()
    {
        return $this->hasManyThrough(BranchStock::class, ProductVariant::class);
    }

    public function inventoryMovements()
    {
        return $this->hasManyThrough(InventoryMovement::class, ProductVariant::class);
    }

    public function batches()
    {
        return $this->hasManyThrough(Batch::class, ProductVariant::class);
    }
}
