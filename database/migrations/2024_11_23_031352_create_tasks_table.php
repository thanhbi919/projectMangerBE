<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('due_date');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('assign_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('priority_id')->constrained('task_priorities')->cascadeOnDelete();
            $table->foreignId('type_id')->constrained('task_types')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('task_status')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
