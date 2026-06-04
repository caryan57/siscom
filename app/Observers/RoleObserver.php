<?php

namespace App\Observers;

use App\Models\Role;

class RoleObserver
{
    public function deleting(Role $role): bool
    {
        if($role->name === 'owner') return false;

        return true;
    }
}
