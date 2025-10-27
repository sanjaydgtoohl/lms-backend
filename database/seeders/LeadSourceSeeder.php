<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lead_source')->insert([
            [
                'name' => 'Social Media',
                'slug' => 'social-media',
                'description' => 'Leads from platforms like Facebook, Instagram, LinkedIn, etc.',
                'status' => '1', // active
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Referral',
                'slug' => 'referral',
                'description' => 'Leads referred by existing customers or partners.',
                'status' => '1',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Website',
                'slug' => 'website',
                'description' => 'Leads generated through website forms or landing pages.',
                'status' => '1',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Newspaper',
                'slug' => 'newspaper',
                'description' => 'Leads collected from newspaper ads and offline campaigns.',
                'status' => '1',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}