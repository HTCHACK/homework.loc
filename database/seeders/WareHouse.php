<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WareHouse extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\WareHouseMaterial\WareHouse::create([
            'name' => 'GMUzbekistan',
        ]);
        \App\Models\WareHouseMaterial\WareHouse::create([
            'name' => 'BMW',
        ]);
    }
}
