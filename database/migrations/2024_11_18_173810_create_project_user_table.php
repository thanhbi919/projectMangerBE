<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng projects
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Khóa ngoại đến bảng users
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null'); // Thêm nullable để khóa ngoại hoạt động với 'set null'
            $table->timestamps();

            // Đảm bảo rằng mỗi user chỉ có 1 vai trò trong một dự án
            $table->unique(['project_id', 'user_id']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
