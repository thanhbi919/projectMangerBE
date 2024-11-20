<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypesTableAndAddTypeIdToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Kiểm tra xem bảng types đã tồn tại chưa
        if (!Schema::hasTable('types')) {
            // Tạo bảng types
            Schema::create('types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        // Kiểm tra xem cột type_id đã tồn tại chưa trước khi thêm vào bảng projects
        if (!Schema::hasColumn('projects', 'type_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('type_id')->nullable()->constrained('types')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Xóa cột type_id khỏi bảng projects nếu có
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id');
        });

        // Xóa bảng types
        Schema::dropIfExists('types');
    }
}

