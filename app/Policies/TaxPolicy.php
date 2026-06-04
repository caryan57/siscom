<?php

namespace App\Policies;

use App\Models\Tax;
use App\Models\User;

class TaxPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Tax');
    }

    public function view(User $user, Tax $tax): bool
    {
        return $user->can('View:Tax');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Tax');
    }

    public function update(User $user, Tax $tax): bool
    {
        if ($tax->is_system) return false;
        return $user->can('Update:Tax');
    }

    public function delete(User $user, Tax $tax): bool
    {
        if ($tax->is_system) return false;
        return $user->can('Delete:Tax');
    }

    public function restore(User $user, Tax $tax): bool
    {
        return false;
    }

    public function forceDelete(User $user, Tax $tax): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
