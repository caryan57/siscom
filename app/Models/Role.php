<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Role extends SpatieRole
{
    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (blank($role->company_id)) {
                if (current_company_id()) {
                    $role->company_id = current_company_id();
                } else {
                    throw new HttpException(400, "No se puede crear un rol sin una empresa asociada.");
                }
            }
        });
    }
}
