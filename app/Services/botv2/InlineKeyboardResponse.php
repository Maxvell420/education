<?php

namespace App\Services\botv2;

use App\Events\ChainCreateEvent;
use App\Models\Chain;
use App\Models\Course;
use App\Models\Globalwork;
use App\Models\User;
use App\Services\GlobalworkService;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class InlineKeyboardResponse extends BotService
{
    private string $text;
//    handle подумать что возвращает для сокращения кода
    public function handle(string $message):bool|string
    {
        try {
            switch ($message){
                case '/available_courses':
                    $this->availableCoursesRequest();
                    return true;
                case '/joined_courses':
                    $this->joinedCoursesRequest();
                    return true;
                case '/courses':
                    $this->coursesForEditing();
                    return true;
                case str_starts_with($message,'/course_update'):
                    $this->courseUpdateRequest(Course::find(substr($message,14)));
                    return true;
                case str_starts_with($message,'/question_list'):
                    $this->questionEditList(Course::find(substr($message,14)));
                    return true;
                case str_starts_with($message, '/course_join'):
                    $this->registerUserInCourse(Course::find(substr($message,13)));
                    return true;
                case (str_starts_with($message,'course')):
                    $this->showCourse(Course::find(substr($message,6)));
                    return true;
                case '/exit':
                    $this->exit();
                    return true;
                case '/editing':
                    $this->editing();
                    return true;
                case '/start':
                    $this->start();
                    return true;
                case '/menu':
                    $this->MainMenu();
                    return true;
//                    Клавиатура админа
            }
        } catch (\Exception $e) {
            $this->MainMenu();
            return $e->getMessage();
        }
        return false;
    }
    public function replyPreparation():array
    {
        $data =[
            'chat_id'=>$this->user->telegram_id,
            'text'=>$this->text??'Попробуй воспользоваться клавиатурой',
        ];
        if (isset($this->keyboard)){
            $data['reply_markup']=$this->keyboard;
        }
        return $data;
    }
    private function MainMenu():void
    {
        $this->text='Выбери из вариантов ниже';
        $this->buttons=[
            Keyboard::inlineButton([['text'=>'Доступные курсы','callback_data'=>'/available_courses']]),
        ];
        if ($this->joinedCourses()->isNotEmpty()){
            $this->buttons[]= Keyboard::inlineButton([['text'=>'Ваши подписки','callback_data'=>'/joined_courses']]);
        }
        if ($this->user->role_id == 2){
            $this->buttons[] = Keyboard::inlineButton([['text' => 'Клавиатура админа', 'callback_data' => '/editing']]);
        }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    private function availableCoursesRequest():void
    {
        $available_courses=$this->availableCourses();
        if ($available_courses->isNotEmpty()) {
            foreach ($available_courses as $course)
            {
                $this->text='Ниже список доступных курсов';
                $this->buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'course'.$course->id]]);
            }
        } else {
            $this->text='Курсов для вступления не осталось';
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/menu']]);
        $this->keyboard=Keyboard::make(
            ['inline_keyboard'=>$this->buttons])->inline();
    }
    private function joinedCoursesRequest():void
    {
        $joinedCourses=$this->joinedCourses();
        foreach ($joinedCourses as $course)
        {
            $this->text='Ниже список твоих курсов';
            $this->buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'course'.$course->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/menu']]);
        $this->keyboard=Keyboard::make(
            ['inline_keyboard'=>$this->buttons])->inline();
    }
    private function registerUserInCourse(Course $course):void
    {
        $collection=$this->courseJoin($course);
        $this->text = 'You already have joined '. $course->courseName;
        foreach ($collection['globalworks'] as $globalwork)
        {
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => $globalwork->question()->first()->title,
                'callback_data'=>'/globalwork'.$globalwork->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/joined_courses']]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    private function showCourse(Course $course):void
    {
        $globalworks = $course->getUsersGlobalworks($this->user->id)->get();
        if ($globalworks->isEmpty()) {
            $this->text = $course->course_info;
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => 'Нажми кнопку чтобы вступить в курс',
                'callback_data'=>'/course_join'.$course->id]]);
            $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/available_courses']]);
        } else {
            $this->text = 'Выбери кнопку ниже';
            foreach ($globalworks as $globalwork)
            {
                $this->buttons[]=Keyboard::inlineButton([[
                    'text' => $globalwork->question()->first()->title,
                    'callback_data'=>'/globalwork'.$globalwork->id]]);
            }
            $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/joined_courses']]);
        }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    private function exit():void
    {
        $this->text='Кнопка вызова меню снизу';
        $this->user->chain()->delete();
        $this->keyboard=Keyboard::remove(['remove_keyboard' => true]);
    }
    private function editing():void
    {
        $this->text='Выберите из опций ниже';
        $this->buttons=[
            Keyboard::inlineButton([['text'=>'Создать курс','callback_data'=>'/course_create']]),
            Keyboard::inlineButton([['text'=>'Редактировать курс','callback_data'=>'/courses']]),
            Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/menu']])
        ];
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    private function coursesForEditing():void
    {
        foreach (Course::all() as $course){
            $this->buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'/course_update'.$course->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/editing']]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
        $this->text='Список курсов доступных для редактирования';
    }
    private function courseUpdateRequest(Course $course):void
    {
        $this->buttons=[
            Keyboard::inlinebutton([['text'=>'Редактировать данные курса','callback_data'=>'/course_edit'.$course->id]]),
            Keyboard::inlinebutton([['text'=>'Добавить к курсу вопрос','callback_data'=>'/question_create'.$course->id]])
        ];
        if ($course->questions()->first()!=null) {
            $this->buttons[] = Keyboard::inlinebutton([['text' => 'Редактировать вопрос','callback_data'=>'/question_list'.$course->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/course_update'.$course->id]]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
        $this->text='Выбери из действий ниже';
    }
    private function questionEditList(Course $course):void
    {
        foreach ($course->questions()->get() as $question) {
            $this->buttons[]=Keyboard::inlineButton([['text'=>$question->title,'callback_data'=>'/question_edit'.$question->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'/course_update'.$course->id]]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
        $this->text = $course->courseName;
    }
    private function start():void
    {
      $this->text = 'Привет! Слева от поля ввода сообщения есть кнопка menu, там ты найдешь команды';
      $this->keyboard = Keyboard::remove(['remove_keyboard' => true]);
    }
}
