<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetBranch
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = Auth::user();

        if (!$authUser instanceof User) {
            return $next($request);
        }

        $companyId = current_company_id();

        if (blank($companyId)) {
            session()->forget('branch_id');

            return $next($request);
        }

        $branchQuery = $authUser->isOwner()
            ? Branch::query()->where('company_id', $companyId)
            : $authUser->branches()
                ->withoutGlobalScope('company')
                ->wherePivot('company_id', $companyId)
                ->where('branches.company_id', $companyId);

        $branchId = session('branch_id');

        $hasAccess = filled($branchId)
            && (clone $branchQuery)->whereKey($branchId)->exists();

        if (!$hasAccess) {
            $branchId = (clone $branchQuery)
                ->orderByDesc('is_default')
                ->value('branches.id');

            if (filled($branchId)) {
                session(['branch_id' => $branchId]);
            } else {
                session()->forget('branch_id');
            }
        }

        return $next($request);
    }
}
