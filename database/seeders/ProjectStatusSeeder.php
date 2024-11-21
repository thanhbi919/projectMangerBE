<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'open', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'inprogress', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'close', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('project_status')->insert($statuses);
    }
}
