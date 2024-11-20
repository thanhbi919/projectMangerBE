<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProjectsTableAddTitleDescriptionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cập nhật bảng projects
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('type', ['labor', 'base', 'other'])->default('labor'); // Cột type với các giá trị labor, base, other
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rollback các thay đổi nếu cần
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('description');
            $table->dropColumn('type');
        });
    }
}
