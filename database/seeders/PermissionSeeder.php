<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::truncate();
        DB::table('permission_role')->truncate();

        $superadminRole=Role::where('name','super-admin')->first();
        $adminRole=Role::where('name','admin')->first();
        $directorRole=Role::where('name','director')->first();

        $material_index = Permission::create(['title' => 'material_access']);
        $material_store = Permission::create(['title' => 'material_create']);
        $material_show = Permission::create(['title' => 'material_show']);
        $material_update = Permission::create(['title' => 'material_update']);
        $material_delete = Permission::create(['title' => 'material_delete']);

        $product_index = Permission::create(['title' => 'product_access']);
        $product_store = Permission::create(['title' => 'product_create']);
        $product_show = Permission::create(['title' => 'product_show']);
        $product_update = Permission::create(['title' => 'product_update']);
        $product_delete = Permission::create(['title' => 'product_delete']);

        $buy_material_index = Permission::create(['title' => 'buy_material_access']);
        $buy_material_store = Permission::create(['title' => 'buy_material_create']);
        $buy_material_show = Permission::create(['title' => 'buy_material_show']);
        $buy_material_update = Permission::create(['title' => 'buy_material_update']);
        $buy_material_delete = Permission::create(['title' => 'buy_material_delete']);

        $buy_material_getitem = Permission::create(['title' => 'buy_material_getitem']);
        $buy_material_getItemhistory = Permission::create(['title' => 'buy_material_getItemhistory']);
        $buy_material_send = Permission::create(['title' => 'buy_material_send']);


        $warehousematerials_index = Permission::create(['title' => 'warehousematerials_access']);
        $warehousematerials_store = Permission::create(['title' => 'warehousematerials_create']);
        $warehousematerials_show = Permission::create(['title' => 'warehousematerials_show']);
        $warehousematerials_update = Permission::create(['title' => 'warehousematerials_update']);
        $warehousematerials_delete = Permission::create(['title' => 'warehousematerials_delete']);


        //SUPER ADMIN

        $material_index->roles()->attach($superadminRole);
        $material_store->roles()->attach($superadminRole);
        $material_show->roles()->attach($superadminRole);
        $material_update->roles()->attach($superadminRole);
        $material_delete->roles()->attach($superadminRole);

        $product_index->roles()->attach($superadminRole);
        $product_store->roles()->attach($superadminRole);
        $product_show->roles()->attach($superadminRole);
        $product_update->roles()->attach($superadminRole);
        $product_delete->roles()->attach($superadminRole);

        $buy_material_index->roles()->attach($superadminRole);
        $buy_material_store->roles()->attach($superadminRole);
        $buy_material_show->roles()->attach($superadminRole);
        $buy_material_update->roles()->attach($superadminRole);
        $buy_material_delete->roles()->attach($superadminRole);

        $buy_material_getitem->roles()->attach($superadminRole);
        $buy_material_getItemhistory->roles()->attach($superadminRole);
        $buy_material_send->roles()->attach($superadminRole);

        $warehousematerials_index->roles()->attach($superadminRole);
        $warehousematerials_store->roles()->attach($superadminRole);
        $warehousematerials_show->roles()->attach($superadminRole);
        $warehousematerials_update->roles()->attach($superadminRole);
        $warehousematerials_delete->roles()->attach($superadminRole);

        //ADMIN

        $material_index->roles()->attach($adminRole);
        $material_store->roles()->attach($adminRole);
        $material_show->roles()->attach($adminRole);
        $material_delete->roles()->attach($adminRole);

        $product_index->roles()->attach($adminRole);
        $product_store->roles()->attach($adminRole);
        $product_show->roles()->attach($adminRole);
        $product_delete->roles()->attach($adminRole);

        $buy_material_index->roles()->attach($adminRole);
        $buy_material_store->roles()->attach($adminRole);
        $buy_material_show->roles()->attach($adminRole);
        $buy_material_delete->roles()->attach($adminRole);

        $warehousematerials_index->roles()->attach($adminRole);
        $warehousematerials_store->roles()->attach($adminRole);
        $warehousematerials_show->roles()->attach($adminRole);
        $warehousematerials_delete->roles()->attach($adminRole);
        
        //DIRECTOR
        $material_index->roles()->attach($directorRole);
        $material_store->roles()->attach($directorRole);
        $material_show->roles()->attach($directorRole);

        $product_index->roles()->attach($directorRole);
        $product_store->roles()->attach($directorRole);
        $product_show->roles()->attach($directorRole);

        $buy_material_index->roles()->attach($directorRole);
        $buy_material_store->roles()->attach($directorRole);
        $buy_material_show->roles()->attach($directorRole);

        $warehousematerials_index->roles()->attach($directorRole);
        $warehousematerials_store->roles()->attach($directorRole);
        $warehousematerials_show->roles()->attach($directorRole);
        
    }
}
