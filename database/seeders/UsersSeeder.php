<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Globalwork;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(1)->has(Globalwork::factory(10))->recycle(Course::factory()->create())->create();
    }
}
