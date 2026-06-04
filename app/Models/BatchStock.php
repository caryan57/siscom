<?php

namespace App\Models;

use App\Models\Traits\Branchable;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class BatchStock extends Model
{
    use Tenantable, Branchable;

    protected $table = 'batch_stock';
    protected $fillable = [
        'company_id',
        'branch_id',
        'batch_id',
        'stock'
    ];

    protected $casts = [
        'stock' => 'decimal:3',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
