<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Model
 */
trait Branchable
{
    public static function bootBranchable(): void
    {
        static::creating(function (Model $model) {

            if (
                Auth::check() &&
                empty($model->branch_id)
            ) {
                $model->branch_id = current_branch_id();
            }
        });

        static::addGlobalScope('branch', function (Builder $builder) {

            if (
                Auth::check() &&
                current_branch_id()
            ) {
                $builder->where(
                    'branch_id',
                    current_branch_id()
                );
            }
        });
    }
}
