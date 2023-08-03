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
            $table->text("correct_answer");
            $table->text("incorrect_answer_1")->nullable();
            $table->text("incorrect_answer_2")->nullable();
            $table->text("incorrect_answer_3")->nullable();
            $table->integer("total_attempts")->default(0);
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
