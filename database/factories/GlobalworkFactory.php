<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GlobalworkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "course_id"=>Course::factory(),
            "question_id"=>Question::factory(),
            "user_id"=>User::factory(),
        ];
    }
}
