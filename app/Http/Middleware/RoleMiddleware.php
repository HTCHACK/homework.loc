<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if(Auth::user() && Role::IS_SUPER_ADMIN){
            return $next($request);
        }
        elseif(Auth::user() && Role::IS_ADMIN){
            return $next($request);
        }
        elseif(Auth::user() && Role::IS_DIRECTOR){
            return $next($request);
        }
        else{
            return response()->json(['message'=>'Permission Denied']);
        }
    }
}
