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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();

            $table->integer('version');
            $table->boolean('is_latest')->default(true);

            $table->longText('content');

            $table->integer('score_ai')->nullable();
            $table->integer('score_teacher')->nullable();
            $table->integer('final_score')->nullable();

            $table->text('feedback_ai')->nullable();
            $table->text('feedback_teacher')->nullable();

            $table->string('status')->default('submitted');

            $table->timestamp('submitted_at')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'task_id', 'version']);
            $table->index(['user_id', 'task_id']);
            $table->index(['task_id', 'is_latest']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
