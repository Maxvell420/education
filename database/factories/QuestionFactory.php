<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
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
            "problem"=>fake()->text,
            "title"=>fake()->word,
            "question_type"=>"test",
            "correct_answer"=>fake()->randomElement(["answer_1","answer_2","answer_3","answer_4"]),
            "answer_1"=>fake()->word,
            "answer_2"=>fake()->word,
            "answer_3"=>fake()->word,
            "answer_4"=>fake()->word,

        ];
    }
}
