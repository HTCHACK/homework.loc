<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CounterAgency;

class CounterAgenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\CounterAgency::factory()->create();
    }
}
