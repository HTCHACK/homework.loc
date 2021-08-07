<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthGates
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
        $user = Auth::user();

        if(!app()->runningInConsole() && $user)
        {
            $roles = Role::with('permissions')->get();

            foreach($roles as $role){
                foreach($role->permissions as $permissions){
                    $permissionsArray[$permissions->title][] = $role->id;
                }
            }

            foreach($permissionsArray as $title => $roles)
            {
                Gate::define($title, function(User $user) use ($roles){
                    return count(array_intersect($user->roles->pluck('id')->toArray(),$roles))>0;
                });
            }

        }
        return $next($request);
    }
}
