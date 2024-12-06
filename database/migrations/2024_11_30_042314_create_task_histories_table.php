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
        Schema::create('task_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id'); // Liên kết với task
            $table->unsignedBigInteger('user_id'); // Người thực hiện thay đổi
            $table->string('action'); // Hành động (e.g., "update_status", "assign_user")
            $table->text('old_value')->nullable(); // Giá trị trước thay đổi
            $table->text('new_value')->nullable(); // Giá trị sau thay đổi
            $table->text('description')->nullable(); // Mô tả chi tiết
            $table->timestamps();

            // Thiết lập khóa ngoại
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_histories');
    }
};
