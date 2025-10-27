<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BrandTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'National',
            'Regional',
            'Local'
        ];

        foreach ($types as $type) {
            DB::table('brand_types')->insert([
                'name' => $type,
                'slug' => Str::slug($type),
                'status' => '1',
                'created_at' => Carbon::now(), // <-- [FIX 2] Ise badlein
                'updated_at' => Carbon::now()  // <-- [FIX 2] Ise badlein
            ]);
        }
    }
}