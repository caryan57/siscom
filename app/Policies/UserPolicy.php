<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    private function belongsToCurrentCompany(User $user): bool
    {
        if(blank(current_company_id())) return false;

        return $user->companies()
            ->whereKey(current_company_id())
            ->exists();
    }
    public function viewAny(User $user): bool
    {
        return $this->belongsToCurrentCompany($user) && $user->can('ViewAny:User');
    }

    public function view(User $user, User $model): bool
    {
        return $this->belongsToCurrentCompany($model)
            && $user->can('View:User');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:User');
    }

    public function update(User $user, User $model): bool
    {
        if (!$this->belongsToCurrentCompany($model)) return false;

        if ($model->isOwner()) return $user->isOwner();

        return $user->can('Update:User');
    }

    public function delete(User $user, User $model): bool
    {
        if (!$this->belongsToCurrentCompany($model)) return false;

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
        if (!$this->belongsToCurrentCompany($model)) return false;

        if ($user->id === $model->id) return false;

        if ($model->isOwner()) return $user->isOwner();

        return $user->can('ForceDelete:User');
    }
}
