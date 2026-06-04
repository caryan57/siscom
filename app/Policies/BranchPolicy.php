<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Branch');
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->can('View:Branch');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Branch');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can('Update:Branch');
    }

    public function delete(User $user, Branch $branch): bool
    {
        if ($branch->is_default || !$branch->isDeletable()) return false;
        return $user->can('Delete:Branch');
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Branch $branch): bool
    {
        return false;
    }
}
