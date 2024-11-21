<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 10 dự án
        Project::factory(10)->create()->each(function ($project) {
            // Lấy một số user ngẫu nhiên
            $users = User::inRandomOrder()->take(rand(2, 5))->pluck('id');

            // Lấy role ngẫu nhiên cho mỗi user
            foreach ($users as $userId) {
                $roleId = Role::inRandomOrder()->first()->id;

                // Gắn user với project qua bảng pivot
                $project->users()->attach($userId, ['role_id' => $roleId]);
            }
        });
    }
}
