<?php

namespace App\Models;

use App\Models\ProductLocation;
use App\Models\Traits\HasCompanyDefault;
use App\Models\Traits\Tenantable;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Override;

class Branch extends Model
{
    use HasCompanyDefault, HasUuid, Tenantable;

    protected $table = 'branches';
    protected $fillable = [
        'uuid',
        'company_id',
        'name',
        'slug',
        'address',
        'email',
        'phone',
        'is_default',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_users')
            ->using(BranchUser::class)
            ->withPivot(['company_id'])
            ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function stocks()
    {
        return $this->hasMany(BranchStock::class)->withoutGlobalScopes();
    }

    public function batchStocks()
    {
        return $this->hasMany(BatchStock::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function productLocations()
    {
        return $this->hasMany(ProductLocation::class);
    }

    public function hasUsers()
    {
        return $this->users()->exists();
    }

    public function hasStockRecords()
    {
        return $this->stocks()->exists();
    }

    public function isDeletable(): bool
    {
        if($this->hasUsers() || $this->hasStockRecords()) return false;

        return true;
    }

    #[Override]
    protected static function booted()
    {
        static::creating(function (Branch $branch) {
            $branch->slug = Str::slug($branch->name);
        });
    }
}
