<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Policies\ProductPolicy;
use Laravel\Passport\Passport;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;



class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {   

        $this->registerPolicies();
        
        Passport::routes();
        // $user=Auth::user();

        // $roles = Role::with('permissions')->get();

        //     foreach($roles as $role){
        //         foreach($role->permissions as $permissions){
        //             $permissionsArray[$permissions->title][] = $role->id;
        //         }
        //     }

        //     foreach($permissionsArray as $title => $roles)
        //     {
        //         Gate::define($title, function(User $user) use ($roles){
        //             return count(array_intersect($user->roles->pluck('id')->toArray(),$roles))>0;
        //         });
        //     }
        
    }
}
