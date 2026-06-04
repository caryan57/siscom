<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use Tenantable;

    protected $table = 'batches';
    protected $fillable = [
        'company_id',
        'product_variant_id',
        'lot_number',
        'manufactured_at',
        'expires_at',
        'cost',
        'status',
    ];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at' => 'date',
        'cost' => 'decimal:2',
        'status' => Status::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stocks()
    {
        return $this->hasMany(BatchStock::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
