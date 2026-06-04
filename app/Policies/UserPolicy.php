<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{    
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:User');
    }

    public function view(User $user): bool
    {
        return $user->can('View:User');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:User');
    }

    public function update(User $user, User $model): bool
    {
        if ($model->isOwner()) return $user->isOwner();

        return $user->can('Update:User');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) return false;
        if ($model->isOwner()) return $user->isOwner();

        return $user->can('Delete:User');
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($user->id === $model->id) return false;
        if ($model->isOwner()) return $user->isOwner();

        return $user->can('ForceDelete:User');
    }
}