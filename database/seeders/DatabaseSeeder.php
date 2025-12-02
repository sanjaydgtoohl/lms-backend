<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            UserSeeder::class,
            LaratrustDummySeeder::class,
            LeadSourceSeeder::class,
            AgencyTypeSeeder::class,
            BrandTypeSeeder::class,
            ZoneSeeder::class,
            CallStatusSeeder::class,
            PrioritySeeder::class,
            StatusSeeder::class,
            BriefStatusSeeder::class,
        ]);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}