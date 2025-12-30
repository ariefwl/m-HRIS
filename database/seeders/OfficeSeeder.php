<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('office_locations')->insert([
            'office_name' => 'Kantor Pusat',
            'latitude' => '-6.991540',
            'longitude' => '110.339086',
            'radius_meters' => '50',
            'is_active' => '1'
        ]);
    }
}
