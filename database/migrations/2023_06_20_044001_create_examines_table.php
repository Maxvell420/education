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
        Schema::create('examines', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->index()->constrained("users");
            $table->foreignId("exam_id")->index()->constrained("exams");
            $table->dateTime("exam_finished_in")->nullable();
            $table->dateTime("examine_expires");
            $table->boolean("examine_closure")->default("false");
            $table->integer("correct_answers_percentage")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examines');
    }
};
