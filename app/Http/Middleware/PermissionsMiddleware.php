<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    
    public function handle(Request $request, Closure $next ,$permission = null)
    {    
        
    
        if(Auth::check()){
            if(Auth::user()->role->permissions()->where('title',$permission)->first()){   
                return $next($request);
                }
        }

        return response(['message'=>'Permission Denied']);
    }
}
