<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Role extends SpatieRole
{
    protected static function booted(): void
    {
        static::addGlobalScope('tenant_roles', function (Builder $builder) {
            if (current_company_id()) {
                $builder->where('roles.company_id', current_company_id());
            }
        });

        static::creating(function (Role $role) {
            if (empty($role->company_id)) {
                if (current_company_id()) {
                    $role->company_id = current_company_id();
                } else {
                    throw new HttpException(400, "No se puede crear un rol sin una empresa asociada.");
                }
            }
        });
    }
}
