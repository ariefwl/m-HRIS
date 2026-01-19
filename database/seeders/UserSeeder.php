<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Arief WL',
                'email' => 'ariefwl@email.com',
                'password' => '$2y$12$c2MzyDqft/C8UcqCpJ8G9.uxEf7zWIeHYQdWDXuyAyCLW1pvevuGW',
                'nik' => '0718',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zehan Aqmar',
                'email' => 'zehan@email.com',
                'password' => '$2y$12$c2MzyDqft/C8UcqCpJ8G9.uxEf7zWIeHYQdWDXuyAyCLW1pvevuGW',
                'nik' => '0719',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kelik Sudjianto',
                'email' => 'kelik@email.com',
                'password' => '$2y$12$c2MzyDqft/C8UcqCpJ8G9.uxEf7zWIeHYQdWDXuyAyCLW1pvevuGW',
                'nik' => '0627',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]
        );
    }
}
