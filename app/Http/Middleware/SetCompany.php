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

            if (!session()->has('company_id')) {

                $company = $authUser->companies()->first();

                if ($company) {
                    session(['company_id' => $company->id]);
                }
            }

            if (session()->has('company_id')) {

                $companyId = session('company_id');
                setPermissionsTeamId($companyId);

                app(PermissionRegistrar::class)
                    ->setPermissionsTeamId($companyId);
            }
        }

        return $next($request);
    }
}