<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgencyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Define the agency types
        $types = [
            'Online',
            'Offline',
            'Both'
        ];

        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'name' => $type,
                'slug' => Str::slug($type),
                'status' => '1',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert the data into the database
        DB::table('agency_type')->insert($data);
    }
}