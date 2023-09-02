<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId("course_id")->index()->constrained("courses");
            $table->text("title");
            $table->text("question_type");
            $table->text("problem");
            $table->text("answer_1")->nullable();
            $table->text("answer_2")->nullable();
            $table->text("answer_3")->nullable();
            $table->text("answer_4")->nullable();
            $table->text('correct_answer');
            $table->integer("num_attempts")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
