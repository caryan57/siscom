<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 */
trait Tenantable
{
    public static function bootTenantable(): void
    {
        static::creating(function (Model $model) {

            if (Auth::check() && blank($model->company_id)) {
                $model->company_id = current_company_id();
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && session()->has('company_id')) {
                $table = $builder->getModel()->getTable();
                $builder->where("{$table}.company_id", current_company_id());
            }
        });
    }
}
