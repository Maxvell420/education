<?php

namespace App\Services;

use App\Events\ChainCreateEvent;
use App\Models\Chain;
use App\Models\Course;
use App\Models\Globalwork;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class BotService extends UserService

{
    private ?array $buttons ;
    private Update $update;
    private ?string $data = null;
    public string $text;
    private ?Keyboard $keyboard;
    private string $type;

    public function __construct(Update $update)
    {
        $this->update = $update;
        if ($update->isType('callback_query')) {
            $this->type='callback_query';
            $this->data=$update->callbackQuery->data;
            $user=User::where('telegram_id',$update->callbackQuery->from->id)->first();
        }
        else {
            $this->type='message';
            $user = User::where('telegram_id',$update->message->from->id)->first();
        }
        parent::__construct($user);
    }
    public function handle()
    {
        $chain = $this->user->chain()->first();
        switch ($this->type){
            case 'callback_query':
                $this->callbackQueryHandle($chain);
                break;
            case 'message':
                $this->messageHandle($chain);
                break;
        }
    }
    private function callbackQueryHandle(Chain $chain=null):void
    {
        if ($this->data=='Menu'){
            $this->MainMenu();
        }
        if (str_starts_with($this->data, 'admin')){
            switch (substr($this->data,5)){
                case '/editing':
                    break;
                case 'empty':
                    echo 1;
            }
        }
        if (str_starts_with($this->data, 'user')){
            switch (substr($this->data,4)){
                case '/available_courses':
                    $this->availableCoursesRequest();
                    break;
                case '/joined_courses':
                    $this->joinedCoursesRequest();
                    break;
            }
        }
        $this->sendMessage();
    }
    private function messageHandle(Chain $chain=null):void
    {
        $message=$this->commandsHandle();
        if (!$message) {
            //            Что-то про цепь
        }
    }
    private function commandsHandle():message|bool
    {
        if ($this->update->message->text=='/menu') {
            $this->MainMenu();
            return $this->sendMessage();
        }
        else return false;
    }
    private function MainMenu():void
    {
        $this->buttons=[
            Keyboard::inlineButton([['text'=>'Доступные курсы','callback_data'=>'user/available_courses']]),
        ];
        if ($this->joinedCourses()->isNotEmpty()){
            $this->buttons[]= Keyboard::inlineButton([['text'=>'Ваши подписки','callback_data'=>'user/joined_courses']]);
        }
        if ($this->user->role_id == 2){
            $this->buttons[] = Keyboard::inlineButton([['text' => 'Редактировать курсы', 'callback_data' => 'admin/editing']]);
        }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
        $this->text='Выбери из вариантов ниже';
    }
    private function availableCoursesRequest():void
    {
        $available_courses=$this->availableCourses();
        if ($available_courses->isNotEmpty()) {
            foreach ($available_courses as $course)
            {
                $this->text='Ниже список доступных курсов';
                $this->buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'user/course/'.$course->id]]);
            }
        } else {
            $this->text='Курсов для вступления не осталось';
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'Menu']]);
        $this->keyboard=Keyboard::make(
            ['inline_keyboard'=>$this->buttons])->inline();
    }
    private function joinedCoursesRequest():void
    {
        $joinedCourses=$this->joinedCourses();
            foreach ($joinedCourses as $course)
            {
                $this->text='Ниже список твоих курсов';
                $this->buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'user/course/'.$course->id]]);
            }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'Menu']]);
        $this->keyboard=Keyboard::make(
            ['inline_keyboard'=>$this->buttons])->inline();
    }

    private function coursesForEditing():void
    {
        foreach (Course::all() as $course){
            $this->buttons=[Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'2:'.$course->id]])];
        }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons]);
        $this->text='Список курсов доступных для редактирования';
    }
    private function courseUpdateRequest(Chain $chain)
    {
        $chain->update(['course_id'=>substr($this->data, 2)]);
        $this->buttons=[
            Keyboard::button([['text'=>'Редактировать данные курса']]),
            Keyboard::button([['text'=>'Добавить к курсу вопрос']])
        ];
            if (Course::find(substr($this->data, 2))->questions()->first()!=null) {
                $this->buttons[] = Keyboard::button([['text' => 'Изменить существующий вопрос']]);
            }
            $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons,'one_time_keyboard'=>true]);
        $this->text='Выбери из действий ниже';
    }
    private function questionCreate(Chain $chain)
    {
        $message = $this->questionCreateHandle($this->update->message->text,$chain);
        $flag = $this->questionChainCheck($chain,$message);
        if ($flag==null) {
            $this->buttons[]=[Keyboard::button([['text'=>'Сохранить вопрос в курс']])];
        }
        $this->buttons=[
            Keyboard::button([['text'=>'Посмотреть данные вопроса']])
        ];
        $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons]);
    }
    private function questionChainCheck(Chain $chain,?string $message):?string
    {
        $chain->question_id??'creating';
        if ($message!=null){
            if ($chain->variable_1==null){
                $chain->update(['variable_1'=>$message]);
                $this->text='название вопроса было сохранено';
            }
            elseif ($chain->variable_2==null) {
                $chain->update(['variable_2'=>$message]);
                $this->text='проблема вопроса была сохранена';
            }
            elseif ($chain->variable_3==null){
                $chain->update(['variable_3'=>$message]);
                $this->text='вариант ответа 1 был сохранен';
            }
            elseif ($chain->variable_4==null){
                $chain->update(['variable_4'=>$message]);
                $this->text='вариант ответа 2 был сохранен';
            }
            elseif ($chain->variable_5==null){
                $chain->update(['variable_5'=>$message]);
                $this->text='вариант ответа 3 был сохранен';
            }
            elseif ($chain->variable_6==null){
                $chain->update(['variable_6'=>$message]);
                $this->text='вариант ответа 4 был сохранен';
            }
            elseif ($chain->variable_7==null){
                $chain->update(['variable_6'=>$message]);
                $this->text='Номер правильного варианта был сохранен';
            }
            else $this->text='Все данные для создания вопроса уже есть';
        }
        if ($chain->variable_2==null) {
            return $this->text=$this->text.'Пришли мне проблему вопроса';
        }
        elseif ($chain->variable_3==null) {
            return $this->text=$this->text.'Пришли мне вариант ответа 1';
        }
        elseif ($chain->variable_4==null) {
            return $this->text=$this->text.'Пришли мне вариант ответа 2';
        }
        elseif ($chain->variable_5==null) {
            return $this->text=$this->text.'Пришли мне вариант ответа 3';
        }
        elseif ($chain->variable_6==null) {
            return $this->text=$this->text.'. Пришли мне вариант ответа 4';
        }
        elseif ($chain->variable_7==null) {
            return $this->text=$this->text.'Пришли мне номер правильного варианта ответа';
        }
        else return null;
    }
    private function questionCreateHandle(string $message,Chain $chain):?string
    {
        if ($message=='Добавить к курсу вопрос') {
            return null;
        } else {
            return $message;
        }
    }
    private function courseCreatePreparation(Chain $chain):void
    {
            $this->text='Приступаем к созданию курса, отправьте мне название создаваемого курса';
            $chain->update(['course_id'=>'creating']);
    }
    private function courseMaking(Chain $chain)
    {
        $message =$this->courseEditingHandle($this->update->message->text,$chain);
        if ($message!=null) {
            if ($chain->variable_1==null) {
                $chain->update(['variable_1' => $message]);
                $this->text = 'Название курса: ' . $chain->variable_1;
                if ($chain->wasChanged(['variable_1']) and $chain->variable_2 == null) {
                    return $this->text = $this->text . '. Пришли мне название курса';
                }
            }
                if ($chain->variable_2==null){
                    $chain->update(['variable_2' => $message]);
                }
                if ($chain->variable_1!=null and $chain->variable_2!=null) {
                    $this->text = 'Название курса:' . $chain->variable_1 . ', Информация о курсе:' . $chain->variable_2;
                    $this->buttons = [
                        Keyboard::button([['text' => 'сохранить курс']]),
                        Keyboard::button([['text' => 'редактировать']])
                    ];
                    $this->keyboard = Keyboard::make(['keyboard' => $this->buttons]);
                }
        }
        if ($message==null) {
            if ($chain->variable_1==null) {
                return $this->text='Пришли название курса';
            }
            if ($chain->variable_1!=null and $chain->variable_2==null) {
                return $this->text='Название курса: ' . $chain->variable_1.'. Пришли мне информацию о курсе';
            }
            if ($chain->variable_2==null) {
                return $this->text='Пришли мне информацию о курсе';
            }
        }
    }
    private function courseEditingHandle(string $message,Chain $chain):?string
    {
        if ($message=='Название курса'){
            $chain->update(['variable_1'=>null]);
            return null;
        }
        if ($message=='Информацию о курсе'){
            $chain->update(['variable_2'=>null]);
            return null;
        }
        if ($message=='Редактировать данные курса')
        {
            $course=Course::find($chain->course_id);
            $chain->update(['variable_1'=>$course->courseName,'variable_2'=>$course->course_info]);
            return $message;
        }
        if ($message=='сохранить курс') {
            Course::query()->create(['courseName'=>$chain->variable_1,'course_info'=>$chain->variable_2]);
            Telegram::triggerCommand('exit',$this->update);
            $this->text='курс был успешно создан, теперь нужно создать к нему вопросы';
            return null;
        }
        if ($message=='редактировать') {
            $this->text='Что именно вы хотите редактировать?';
            $this->buttons=[
                Keyboard::button([['text'=>'Название курса']]),
                Keyboard::button([['text'=>'Информацию о курсе']])
            ];
            $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons]);
            return null;
        }
        else return $message;
    }
    private function showCourse(Course $course):void
    {
        $globalworks = $course->getUsersGlobalworks($this->user->id)->get();
        if ($globalworks->isEmpty()){
            $this->text = $course->course_info;
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => 'click this button for joining this course',
                'callback_data'=>'2'.':'.$course->id]]);
        }
        else {
            $this->text = 'choose button bellow';
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => 'question where you have stopped',
                'callback_data'=>'3'.':'.$globalworks->where('answer_check','=',false)->min('id')]]);
            $x=0;
                foreach ($globalworks as $globalwork)
                    {
                        $this->buttons[]=Keyboard::inlineButton([[
                            'text' => $x++,
                            'callback_data'=>'3'.':'.$globalwork->id]]);
                    }
            }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    public function sendMessage():Message
    {
        if ($this->update->isType('callback_query')){
            return Telegram::editMessageReplyMarkup(
                [
                    'chat_id'=>$this->user->telegram_id,
                    'message_id'=>$this->update->callbackQuery->message->messageId,
                    'reply_markup' => $this->keyboard
                ]);
        }
        else {
            $message=
                [
                    'text'=>$this->text,
                    'chat_id'=>$this->user->telegram_id
                ];
            if (isset($this->keyboard)){
                $message['reply_markup'] = $this->keyboard;
            }
            return Telegram::sendMessage($message);
        }
    }
    private function registerUserInCourse(Course $course):void
    {
        $collection=$this->courseJoin($course);
        $this->text = $collection['text']?
            'You have successful joined course'. ' ' . $course->courseName:
            'You already have joined '. $course->courseName;
        $this->buttons[]=Keyboard::inlineButton([[
            'text' => 'proceed joined course',
            'callback_data'=>'3'.':'.$collection['globalwork']]]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();

    }
    private function ChainCreate(Model $model):Chain
    {
        $chain = $model->chain()->firstOrCreate(['user_id'=>$this->user->id],['user_id'=>$this->user->id,class_basename($model).'_id'=>$model->id]);
        if ($chain->wasRecentlyCreated){
            event(new ChainCreateEvent($this,$chain));
        }
        $this->buttons=[Keyboard::button([['text'=>'/exit']])];
        return $chain;
    }
    public function exitMode()
    {
        $this->user->chain()->delete();
        $this->keyboard = Keyboard::remove(['remove_keyboard'=>true]);
        $this->text='you have exited current mode';
    }
    private function getGlobalworkQuestionData(int $globalwork_id):void
    {
        $globalwork=Globalwork::find($globalwork_id);
        $globalworkService= new GlobalworkService($globalwork);
        $this->ChainCreate($globalwork);
        $question=$globalworkService->GlobalworkShowData()['question'];
        array_push($this->buttons,
            Keyboard::button([['text'=>$question->answer_4]]),
            Keyboard::button([['text'=>$question->answer_3]]),
            Keyboard::button([['text'=>$question->answer_2]]),
            Keyboard::button([['text'=>$question->answer_1]])
        );
        $this->text=$question->problem;
        $this->keyboard=Keyboard::make(['keyboard'=>array_reverse($this->buttons),'resize_keyboard'=>true]);
    }
    private function user_answer_check(bool $flag, $chain):void
    {
        if ($flag) {
            $this->sendMessage('Good job! Answer is correct, now sending to you next question');
            $globalwork=Globalwork::where('user_id',$this->user->id)->where('answer_check',false)->first();
            if ($globalwork!=null) {
                $chain->update(['globalwork_id'=>$globalwork->id]);
                $this->getGlobalworkQuestionData($chain->globalwork_id);
            }
            else {
                $this->text = 'it seem you have completed this course, good job';
                Telegram::triggerCommand('exit',$this->update);
            }
        }
        else {
            $this->text = 'Answer is not correct, try again';
        }
    }
    public function unknown():void
    {
        $this->text = 'i dont understand';
    }
    private function editing()
    {
        Chain::create(['user_id'=>$this->user->id,'admin'=>true]);
        $this->text='выберите из опций ниже';
        $this->buttons=[
            Keyboard::inlineButton([['text'=>'создать курс','callback_data'=>0]]),
            Keyboard::inlineButton([['text'=>'редактировать курс','callback_data'=>1]])
        ];
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons]);
    }
    public function adminKeyboard(){
        $this->text='Для выхода из режима Администратора вызовите команду exit';
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Войти в режим администратора','callback_data'=>0]]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons]);
    }
}
