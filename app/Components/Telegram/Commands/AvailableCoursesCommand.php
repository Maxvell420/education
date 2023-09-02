<?php

namespace App\Components\Telegram\Commands;

use App\Models\Course;
use App\Models\User;
use App\Services\BotService;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class AvailableCoursesCommand extends Command
{
    protected string $name = 'available_courses';
    protected string $description = 'returns list of available courses for user';
    public function handle(): void
    {
        $botService = new BotService($this->getUpdate());
        $available_courses=$botService->availableCourses();
        if ($available_courses->isNotEmpty())
            foreach ($available_courses as $course)
            {
                $buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'1:'.$course->id]]);
                $reply_markup=Keyboard::make(
                    ['inline_keyboard'=>$buttons]);
                $this->replyWithMessage(
                    [
                        'text' => 'bellow there are list of available courses for joining',
                        'reply_markup' => $reply_markup]
                );
            }
        else {
            $this->replyWithMessage(
                ['text' => 'there are no courses for you to joining left']
            );
        }
    }
}
