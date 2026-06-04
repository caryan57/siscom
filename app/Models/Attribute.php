<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use Tenantable;

    protected $table = 'attributes';

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function variantValues()
    {
        return $this->hasManyThrough(
            ProductVariantValue::class,
            AttributeValue::class,
            'attribute_id',
            'attribute_value_id'
        );
    }
}
