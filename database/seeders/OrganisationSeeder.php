<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use Illuminate\Support\Str;

/**
 * Organisation Seeder
 * -----------------------------------------
 * This seeder populates the organisations table with default records,
 * including name, slug, and status values.
 *
 * @package Database\Seeders
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $organisations = [
            'DGTOOHL',
            'MOBIYOUNG',
            'JUST BAAT',
            'TRIOOH',
            'TSM',
        ];

        $data = [];

        foreach ($organisations as $name) {
            $data[] = [
                'name'       => $name,
                'slug'       => Str::slug($name),
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Organisation::upsert($data, ['name', 'slug'], ['status', 'updated_at']);
    }
}
