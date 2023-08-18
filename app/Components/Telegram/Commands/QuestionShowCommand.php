<?php

namespace App\Components\Telegram\Commands;

use App\Models\Question;
use App\Services\GlobalworkService;
use Telegram\Bot\Commands\Command;
class QuestionShowCommand extends Command
{
    protected string $name = 'joinedCourses';
    protected string $description = 'returns list of joined courses for user';

    public function handle()
    {
        $update=$this->getUpdate();
        $question = Question::find(substr($update->callbackQuery->data,2));
        $course=$question->course()->first();
        $data = new GlobalworkService();

    }
}
