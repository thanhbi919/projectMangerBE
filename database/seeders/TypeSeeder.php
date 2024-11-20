<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Thêm dữ liệu mẫu vào bảng types
        DB::table('types')->insert([
            ['name' => 'Labor Base'],
            ['name' => 'Other'],
            ['name' => 'Dev Base'],
        ]);
    }
}
