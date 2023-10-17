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
        Schema::create('globalworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->index()->constrained("users");
            $table->foreignId("course_id")->index()->constrained("courses");
            $table->foreignId("examine_id")->nullable()->index()->constrained("examines");
            $table->foreignId("question_id")->index()->constrained("questions")->onDelete('cascade');
            $table->text("user_answer")->nullable();
            $table->boolean("answer_check")->default(false);
            $table->integer("num_attempts")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('globalworks');
    }
};
