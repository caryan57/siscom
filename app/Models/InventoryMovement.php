<?php

namespace App\Models;

use App\Enums\InventoryMovementType;
use App\Models\Traits\Branchable;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use Tenantable, Branchable;

    protected $table = 'inventory_movements';
    protected $fillable = [
        'company_id',
        'branch_id',
        'product_variant_id',
        'user_id',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'batch_id',
        'type',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'stock_before' => 'decimal:3',
        'stock_after' => 'decimal:3',
        'type' => InventoryMovementType::class,
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
        return $this->belongsTo(ProductVariant::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
