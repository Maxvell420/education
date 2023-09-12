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
        Schema::create('chains', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->index()->unique()->constrained("users");
            $table->boolean('admin')->default(false);
            $table->foreignId('globalwork_id')->nullable()->constrained('globalworks');
            $table->text('course_id')->nullable()->constrained('courses');
            $table->text('question_id')->nullable()->constrained('questions');
            $table->text('variable_1')->nullable();
            $table->text('variable_2')->nullable();
            $table->text('variable_3')->nullable();
            $table->text('variable_4')->nullable();
            $table->text('variable_5')->nullable();
            $table->text('variable_6')->nullable();
            $table->text('variable_7')->nullable();
            $table->text('variable_8')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chains');
    }
};
