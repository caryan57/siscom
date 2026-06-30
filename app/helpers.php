<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('current_company_id')) {
    function current_company_id(): ?int
    {
        return session('company_id');
    }
}

if (!function_exists('current_branch_id')) {
    function current_branch_id(): ?int
    {
        return session('branch_id');
    }
}

if (!function_exists('current_user_is_owner')) {
    function current_user_is_owner(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !current_company_id()) return false;
        return $user->isOwner();
    }
}
