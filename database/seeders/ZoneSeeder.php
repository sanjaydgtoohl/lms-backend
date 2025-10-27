<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;      
use Carbon\Carbon;               

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            'North Zone',
            'South Zone',
            'East Zone',
            'West Zone',
            'Central Zone'
        ];

        foreach ($zones as $zone) {
            DB::table('zones')->insert([
                'name' => $zone,
                'slug' => Str::slug($zone),
                'status' => '1',           
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}