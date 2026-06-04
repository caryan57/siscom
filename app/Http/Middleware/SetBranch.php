<?php

namespace App\Http\Middleware;

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

        if($authUser instanceof User) {
            if(!session()->has('branch_id')) {

                $branch = $authUser->branches()
                ->withoutGlobalScope('company')
                ->where('branches.company_id', current_company_id())
                ->first();

                if($branch) {
                    session(['branch_id' => $branch->id]);
                }
            }
        }

        return $next($request);
    }
}
