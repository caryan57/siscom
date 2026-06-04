<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Override;

class Company extends Model
{
    use HasUuid;

    protected $table = 'companies';
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'email',
        'phone',
        'logo',
        'tax_identifier',
        'timezone',
        'currency',
        'country',
        'status',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $casts = [
        'status' => Status::class
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->using(CompanyUser::class)
            ->withTimestamps();
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function priceLists()
    {
        return $this->hasMany(PriceList::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function batchStocks()
    {
        return $this->hasMany(BatchStock::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function productLocations()
    {
        return $this->hasMany(ProductLocation::class);
    }

    #[Override]
    protected static function booted()
    {
        static::creating(function (Company $company) {
            $company->slug = Str::slug($company->name);
        });
    }
}
