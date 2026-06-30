<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class SetCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = Auth::user();

        if ($authUser instanceof User) {
            $companyId = session('company_id');
            $hasAccess = filled($companyId) && $authUser->companies()->whereKey($companyId)->exists();

            if (!$hasAccess) {
                $companyId = $authUser->companies()->value('companies.id');
                session(['company_id' => $companyId]);
                session()->forget('branch_id');
            }

            setPermissionsTeamId($companyId);
            app(PermissionRegistrar::class)
                ->setPermissionsTeamId($companyId);
        }
        return $next($request);
    }
}
