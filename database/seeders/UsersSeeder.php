<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;


class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        User::truncate();

        // $superadminRole=Role::where('name','super-admin')->first();
        // $adminRole=Role::where('name','admin')->first();
        // $directorRole=Role::where('name','director')->first();

        $superadmin=User::create([
            'name'=>'super-admin',
            'email'=>'superadmin@gmail.com',
            'role_id'=>'1',
            'password'=>Hash::make('@superadmin@')
        ]);

        
        $admin=User::create([
            'name'=>'admin',
            'email'=>'admin@gmail.com',
            'role_id'=>'2',
            'password'=>Hash::make('@adminadmin@')
        ]);

        
        $director=User::create([
            'name'=>'director',
            'email'=>'director@gmail.com',
            'role_id'=>'3',
            'password'=>Hash::make('@directortech@')
        ]);


        // $superadmin->roles()->attach($superadminRole);

        // $admin->roles()->attach($adminRole);

        // $director->roles()->attach($directorRole);
    }
}
