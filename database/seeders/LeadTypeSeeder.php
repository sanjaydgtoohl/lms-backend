<?php

/**
 * LeadType Seeder
 * -----------------------------------------
 * Seeds the lead_types table with predefined lead types.
 * Utilizes upsert to ensure idempotent seeding and prevent duplicates.
 *
 * @package Database\Seeders
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeadType;
use Illuminate\Support\Str;

class LeadTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $leadTypes = [
            'BRAND',
            'AGENCY',
        ];

        $data = [];

        foreach ($leadTypes as $name) {
            $data[] = [
                'name'       => $name,
                'slug'       => Str::slug($name),
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        LeadType::upsert($data, ['name', 'slug'], ['status', 'updated_at']);
    }
}
