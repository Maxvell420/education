<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use Telegram\Bot\Commands\Command;
class CourseJoinCommand extends Command
{
    public function handle()
    {
        $update= $this->getUpdate();
        $course=Course::find(substr($update->callbackQuery->data,2));
    }
}
