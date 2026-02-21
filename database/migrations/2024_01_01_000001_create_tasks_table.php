<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('pending');
            $table->string('assignee')->nullable();
            $table->unsignedBigInteger('project_id');
            $table->boolean('refined')->default(false);
            $table->json('refinement_output')->nullable();
            $table->boolean('bypass_refinement')->default(false);
            $table->timestamps();

            $table->index(['status', 'refined']);
            $table->index('project_id');
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('blocked_by_task_id')->constrained('tasks')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['task_id', 'blocked_by_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
    }
};