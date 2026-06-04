<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PriceList;
use App\Models\User;

class PriceListPolicy
{
    
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:PriceList');
    }

    public function view(User $user, PriceList $priceList): bool
    {
        return $user->can('View:PriceList');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:PriceList');
    }

    public function update(User $user, PriceList $priceList): bool
    {
        if($priceList->is_system) return false;

        return $user->can('Update:PriceList');
    }

    public function delete(User $user, PriceList $priceList): bool
    {
        if($priceList->is_system) return false;
        return $user->can('Delete:PriceList');
    }

    public function restore(User $user, PriceList $priceList): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}