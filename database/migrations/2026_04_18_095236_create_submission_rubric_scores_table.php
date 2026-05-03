<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('submission_rubric_scores', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('submission_id')
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId('task_rubric_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('score_ai')->nullable();
            $table->integer('score_teacher')->nullable();

            $table->text('feedback_ai')->nullable();
            $table->text('feedback_teacher')->nullable();

            $table->timestamps();

            $table->unique(['submission_id', 'task_rubric_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_rubric_scores');
    }
};
