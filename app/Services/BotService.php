<?php

namespace App\Services;

use App\Events\ChainCreateEvent;
use App\Models\Chain;
use App\Models\Course;
use App\Models\Globalwork;
use App\Models\Question;
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
    public function handle():void
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
    private function callbackQueryHandle():void
    {
        if ($this->data=='Menu'){
            $this->MainMenu();
        }
        if (str_starts_with($this->data, 'admin')) {
            switch (substr($this->data,5)) {
                case '/editing':
                    $this->editing();
                    break;
                case '/course_create':
                    $this->courseCreateOrUpdatePreparation();
                    break;
                case '/course_edit':
                    $this->coursesForEditing();
                    break;
                case (substr($this->data,5,12)=='/course_info'):
                    $this->courseCreateOrUpdatePreparation(Course::find(substr($this->data,18)));
                    break;
                case (substr($this->data,5,16)=='/question_create'):
                    $this->questionUpdateOrCreatePreparation(substr($this->data,22));
                    break;
                case (substr($this->data,5,14)=='/question_list'):
                    $this->questionEditList(Course::find(substr($this->data,20)));
                    break;
                case (substr($this->data,5,14))=='/question_edit':
                    $question=Question::find(substr($this->data,20));
                    $this->questionUpdateOrCreatePreparation($question->course_id,$question);
                    break;
                case (substr($this->data,5,7)=='/course'):
                    $this->courseUpdateRequest(Course::find(substr($this->data,13)));
                    break;
            }
        }
        if (str_starts_with($this->data, 'user')) {
            switch (substr($this->data,4)) {
                case '/available_courses':
                    $this->availableCoursesRequest();
                    break;
                case '/joined_courses':
                    $this->joinedCoursesRequest();
                    break;
                case (substr($this->data,4,12)=='/course_join'):
                    $this->registerUserInCourse(Course::find(substr($this->data,17)));
                    break;
                case (substr($this->data,4,7)=='/course'):
                    $this->showCourse(Course::find(substr($this->data,12)));
                    break;
                case (substr($this->data,4,11)=='/globalwork'):
                    $this->getGlobalworkQuestionData(substr($this->data,16));
                    break;
            }
        }
        $this->sendMessage();
    }
    private function messageHandle():void
    {
        $chain=$this->user->chain()->first();
        $message=$this->commandsHandle();
        if (!$message and isset($chain)) {
            if (!$chain->admin){
                $globalworkService = new GlobalworkService(Globalwork::find($chain->globalwork_id));
                $globalworkService->GlobalworkUpdate($this->update->message->text);
                $this->user_answer_check($globalworkService->answerCheck(),$chain);
                $this->sendMessage();
            }
            if ($chain->question_id==null){
                $this->courseMaking($chain);
                $this->sendMessage();
            }
            if ($chain->question_id!=null){
                $this->questionCreateOrUpdateMenu($chain,$this->update->message->text??null);
                $this->sendMessage();
            }
        } else {
          $this->unknown();
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
        if ($this->user->chain()->first() !== null){
            Telegram::triggerCommand('exit',$this->update);
        }
        $this->buttons=[
            Keyboard::inlineButton([['text'=>'Доступные курсы','callback_data'=>'user/available_courses']]),
        ];
        if ($this->joinedCourses()->isNotEmpty()){
            $this->buttons[]= Keyboard::inlineButton([['text'=>'Ваши подписки','callback_data'=>'user/joined_courses']]);
        }
        if ($this->user->role_id == 2){
            $this->buttons[] = Keyboard::inlineButton([['text' => 'Клавиатура админа', 'callback_data' => 'admin/editing']]);
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
            $this->buttons[]=Keyboard::inlineButton([['text'=>$course->courseName,'callback_data'=>'admin/course/'.$course->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'admin/editing']]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
        $this->text='Список курсов доступных для редактирования';
    }
    private function courseUpdateRequest(Course $course):void
    {
        $this->buttons=[
            Keyboard::inlinebutton([['text'=>'Редактировать данные курса','callback_data'=>'admin/course_info/'.$course->id]]),
            Keyboard::inlinebutton([['text'=>'Добавить к курсу вопрос','callback_data'=>'admin/question_create/'.$course->id]])
        ];
            if ($course->questions()->first()!=null) {
                $this->buttons[] = Keyboard::inlinebutton([['text' => 'Редактировать вопрос','callback_data'=>'admin/question_list/'.$course->id]]);
            }
            $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'admin/course_edit']]);
            $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
        $this->text='Выбери из действий ниже';
    }
    private function questionCreateOrUpdateMenu(Chain $chain,string $message=null): void
    {
        $message = $this->questionChainCheck($chain,$message);
        if ($message==null and Chain::find($chain->id)!=null) {
            $this->buttons[]=Keyboard::button([['text'=>'Сохранить вопрос в курс']]);
            $this->buttons[]= Keyboard::button([['text'=>'Посмотреть данные вопроса']]);
            $this->buttons[]= Keyboard::button([['text'=>'Редактировать']]);
            $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons,'one_time_keyboard'=>true]);
        }
    }
    private function questionUpdateOrCreatePreparation(int $course_id,Question $question=null):void
    {
        $chain=$this->user->chain()->create(['admin'=>true,
            'course_id'=>$course_id,
            'question_id'=>$question->id??-1,
            'variable_1'=>$question->title??null,
            'variable_2'=>$question->problem??null,
            'variable_3'=>$question->answer_1??null,
            'variable_4'=>$question->answer_2??null,
            'variable_5'=>$question->answer_3??null,
            'variable_6'=>$question->answer_4??null,
            'variable_7'=>substr($question->correct_answer??null,-1),
            'variable_8'=>$question->downloads()->first()->file_id??null,
            ]);
        if ($question!=null) {
            $this->questionCreateOrUpdateMenu($chain,'Посмотреть данные вопроса');
        } else {
            $this->text='Приступаем к созданию для Вопроса. Отправь мне тему вопроса';
        }
    }
    private function chainCheck(Chain $chain):void
    {
        $this->text='Тема вопроса:'.$chain->variable_1;
        $this->text=$this->text.'. Проблема вопроса:'.$chain->variable_2;
        $this->text=$this->text.'. Вариант ответа 1:'.$chain->variable_3;
        $this->text=$this->text.'. Вариант ответа 2:'.$chain->variable_4;
        $this->text=$this->text.'. Вариант ответа 3:'.$chain->variable_5;
        $this->text=$this->text.'. Вариант ответа 4:'.$chain->variable_6;
        $this->text=$this->text.'. Номер правильного варианта:'.$chain->variable_7;
        if (isset($chain->variable_8)){
            Telegram::sendPhoto(['chat_id'=>$this->user->telegram_id,'photo'=>$chain->variable_8]);
        }
    }
    private function chainQuestionRequest(Chain $chain):\Illuminate\Http\Request
    {
        $request = request()->merge([
            'question_id'=>$chain->question_id,
            'title'=>$chain->variable_1,
            'problem'=>$chain->variable_2,
            'answer_1'=>$chain->variable_3,
            'answer_2'=>$chain->variable_4,
            'answer_3'=>$chain->variable_5,
            'answer_4'=>$chain->variable_6,
            'correct_answer'=>$chain->variable_7,
        ]);
        if (isset($chain->variable_8)){
            $request->merge(['file_id'=>$chain->variable_8]);
        }
        $request->headers->set('Accept','application/json');
        $request->headers->set('Content-Type','application/json');
        return $request;
    }
    private function questionChainCommandsCheck(string $message=null, Chain $chain):?string
    {
        if ($message=='Посмотреть данные вопроса') {
            $this->chainCheck($chain);
            return null;
        }
        if ($message=='Сохранить вопрос в курс') {
            $request=$this->chainQuestionRequest($chain);
            $question= new QuestionService();
            $course=Course::find($chain->course_id);
            try {
                $question->questionCreate($request,$course);
            }
            catch (\Exception $e) {
                $this->text=$e->getMessage();
            }
            if (!isset($this->text)) {
                $this->text='Изменения вопроса сохранены';
                $this->exitMode($chain);
            }
            return null;
        }
        if ($message=='Редактировать') {
            return $this->chainQuestionEditMenu();
        }
        if ($message=='Тему вопроса') {
            $chain->update(['variable_1'=>null]);
        }
        if ($message=='Проблему вопроса'){
            $chain->update(['variable_2'=>null]);
        }
        if ($message=='Вариант ответа 1'){
            $chain->update(['variable_3'=>null]);
        }
        if ($message=='Вариант ответа 2'){
            $chain->update(['variable_4'=>null]);
        }
        if ($message=='Вариант ответа 3'){
            $chain->update(['variable_5'=>null]);
        }
        if ($message=='Вариант ответа 4'){
            $chain->update(['variable_6'=>null]);
        }
        if ($message=='Номер правильного варианта'){
            $chain->update(['variable_7'=>null]);
        }
        if ($message=='Изображение к вопросу'){
            $chain->update(['variable_8'=>null]);
        }
        if ($chain->wasChanged()){
            $this->text='Принято';
            return null;
        }
        else return $message;
    }
    private function chainQuestionEditMenu()
    {
        $this->text='Что вы хотите редактировать?';
        $this->buttons[]=Keyboard::button([['text'=>'Тему вопроса']]);
        $this->buttons[]=Keyboard::button([['text'=>'Проблему вопроса']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 1']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 2']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 3']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 4']]);
        $this->buttons[]=Keyboard::button([['text'=>'Номер правильного варианта']]);
        $this->buttons[]=Keyboard::button([['text'=>'Изображение к вопросу']]);
        return null;
    }
    private function questionChainCheck(Chain $chain,?string $message=null):?string
    {
        $message = $this->questionChainCommandsCheck($message,$chain);
        if ($message!=null) {
            if ($chain->variable_1==null) {
                $chain->update(['variable_1'=>$message]);
                $this->text='Тема вопроса было сохранена';
            }
            elseif ($chain->variable_2==null) {
                $chain->update(['variable_2'=>$message]);
                $this->text='проблема вопроса была сохранена';
            }
            elseif ($chain->variable_3==null) {
                $chain->update(['variable_3'=>$message]);
                $this->text='вариант ответа 1 был сохранен';
            }
            elseif ($chain->variable_4==null) {
                $chain->update(['variable_4'=>$message]);
                $this->text='вариант ответа 2 был сохранен';
            }
            elseif ($chain->variable_5==null) {
                $chain->update(['variable_5'=>$message]);
                $this->text='вариант ответа 3 был сохранен';
            }
            elseif ($chain->variable_6==null) {
                $chain->update(['variable_6'=>$message]);
                $this->text='вариант ответа 4 был сохранен';
            }
            elseif ($chain->variable_7==null) {
                $chain->update(['variable_7'=>$message]);
                $this->text='Номер правильного варианта был сохранен';
            }
            else $this->text='Все данные для создания вопроса уже есть';
        }
        if (isset($this->update->message->photo)) {
            foreach ($this->update->message->photo as $photo){
                $file_id=$photo->file_id;
                break;
            }
            $chain->update(['variable_8'=>$file_id]);
            return $this->text='Изображение было сохранено';
        }
        if ($chain->variable_1==null) {
            return $this->text=$this->text.'. Пришли мне тему вопроса';
        }
        elseif ($chain->variable_2==null) {
            return $this->text=$this->text.'. Пришли мне проблему вопроса';
        }
        elseif ($chain->variable_3==null) {
            return $this->text=$this->text.'. Пришли мне вариант ответа 1';
        }
        elseif ($chain->variable_4==null) {
            return $this->text=$this->text.'. Пришли мне вариант ответа 2';
        }
        elseif ($chain->variable_5==null) {
            return $this->text=$this->text.'. Пришли мне вариант ответа 3';
        }
        elseif ($chain->variable_6==null) {
            return $this->text=$this->text.'. Пришли мне вариант ответа 4';
        }
        elseif ($chain->variable_7==null) {
            return $this->text=$this->text.'. Пришли мне номер правильного варианта ответа';
        }
        if ($chain->variable_8==null){
            $this->text=$this->text.' Можешь прислать мне изображение к вопросу';
        }
        return null;
    }
    private function questionEditList(Course $course)
    {
        foreach ($course->questions()->get() as $question) {
            $this->buttons[]=Keyboard::inlineButton([['text'=>$question->title,'callback_data'=>'admin/question_edit/'.$question->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'admin/course/'.$course->id]]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    private function courseCreateOrUpdatePreparation(Course $course=null):void
    {
        $chain=$this->user->chain()->create(['admin'=>true,'course_id'=>$course->id??-1]);
        if ($course!=null) {
            $this->courseMaking($chain);
        } else {
            $this->text='Приступаем к созданию курса, отправьте мне название создаваемого курса';
        }
    }
    private function courseMaking(Chain|Model $chain)
    {
        $message =$this->courseEditingHandle($chain,$this->update->message->text??null);
        if ($message!=null) {
            if ($chain->variable_1==null) {
                $chain->update(['variable_1' => $message]);
                $this->text = 'Название курса: ' . $chain->variable_1;
                if ($chain->wasChanged(['variable_1']) and $chain->variable_2 == null) {
                    return $this->text = $this->text . '. Пришли мне Информацию о курсе';
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
                    $this->keyboard = Keyboard::make(['keyboard' => $this->buttons,'one_time_keyboard'=>true]);
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
    private function courseEditingHandle(Chain $chain,string $message=null):?string
    {
        if ($message=='Название курса'){
            $chain->update(['variable_1'=>null]);
            return null;
        }
        if ($message=='Информацию о курсе'){
            $chain->update(['variable_2'=>null]);
            return null;
        }
        if ($message=='сохранить курс') {
            Course::query()->updateOrCreate(['id'=>$chain->course_id],['courseName'=>$chain->variable_1,'course_info'=>$chain->variable_2]);
            $this->text='Изменения курса сохранены';
            $this->exitMode($chain);
            return null;
        }
//        отработает ли try catch если при создании курса отправить сообщение сохранить курс?
        if ($message=='редактировать') {
            $this->text='Что именно вы хотите редактировать?';
            $this->buttons=[
                Keyboard::button([['text'=>'Название курса']]),
                Keyboard::button([['text'=>'Информацию о курсе']])
            ];
            $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons,'one_time_keyboard'=>true]);
            return null;
        }
        if (is_int(intval($chain->course_id)) and !is_null($chain->variable_1) and !is_null($chain->variable_2))
        {
            $course=Course::find($chain->course_id);
            $chain->update(['variable_1'=>$course->courseName,'variable_2'=>$course->course_info]);
            return true;
        }
        else return $message;
    }
    private function showCourse(Course $course):void
    {
        $globalworks = $course->getUsersGlobalworks($this->user->id)->get();
        if ($globalworks->isEmpty()) {
            $this->text = $course->course_info;
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => 'Нажми кнопку чтобы вступить в курс',
                'callback_data'=>'user/course_join/'.$course->id]]);
            $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'user/available_courses']]);
        } else {
            $this->text = 'Выбери кнопку ниже';
                foreach ($globalworks as $globalwork)
                    {
                        $this->buttons[]=Keyboard::inlineButton([[
                            'text' => $globalwork->question()->first()->title,
                            'callback_data'=>'user/globalwork/'.$globalwork->id]]);
                    }
            $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'user/joined_courses']]);
        }
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    public function sendMessage():Message
    {
        if ($this->update->isType('callback_query') and isset($this->keyboard)) {
            if ($this->keyboard->isInlineKeyboard()) {
                return Telegram::editMessageReplyMarkup(
                    [
                        'chat_id' => $this->user->telegram_id,
                        'message_id' => $this->update->callbackQuery->message->messageId,
                        'reply_markup' => $this->keyboard
                    ]);
            }
            else {
                return Telegram::sendMessage(
                    [
                        'text'=>$this->text,
                        'chat_id' => $this->user->telegram_id,
                        'message_id' => $this->update->callbackQuery->message->messageId,
                        'reply_markup' => $this->keyboard
                    ]);
            }
        }
        else {
            $message=
                [
                    'text'=>$this->text,
                    'chat_id'=>$this->user->telegram_id
                ];
            if (isset($this->keyboard)) {
                $message['reply_markup'] = $this->keyboard;
            }
            return Telegram::sendMessage($message);
        }
    }
    private function registerUserInCourse(Course $course):void
    {
        $collection=$this->courseJoin($course);
        $this->text = 'You already have joined '. $course->courseName;
        $x=1;
        foreach ($collection['globalworks'] as $globalwork)
        {
            $this->buttons[]=Keyboard::inlineButton([[
                'text' => $x++,
                'callback_data'=>'user/globalwork/'.$globalwork->id]]);
        }
        $this->buttons[]=Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'user/joined_courses']]);
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
    private function ChainCreate(User $user,Model $model=null):Chain|model
    {
        $chain = $user->chain()->updateOrCreate(['user_id'=>$user->id],[strtolower(class_basename($model)).'_id'=>$model->id]);
        if ($chain->wasRecentlyCreated){
            event(new ChainCreateEvent($this,$chain));
        }
        $this->buttons=[Keyboard::button([['text'=>'/exit']])];
        return $chain;
    }
    public function exitMode(Chain $chain=null)
    {
        $chain= $this->user->chain()->first()??$chain;
        if ($chain!=null){
            if (!$chain->admin and $chain->globalwork_id!=null){
                $this->text='Вы вышли из режима ответов на вопросы';
            }
            $chain->delete();
        }
        $this->keyboard = Keyboard::remove(['remove_keyboard'=>true]);;
    }
    private function getGlobalworkQuestionData(int $globalwork_id):void
    {
        $globalwork=Globalwork::find($globalwork_id);
        $globalworkService= new GlobalworkService($globalwork);
        $this->ChainCreate($this->user,$globalwork);
        $question=$globalworkService->GlobalworkShowData()['question'];
        $photo=$question->downloads()->first();
        if ($photo!=null) {
            Telegram::sendPhoto(['chat_id'=>$this->user->telegram_id,'photo'=>$photo->file_id]);
        }
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
            $this->text='Правильный ответ';
            $this->sendMessage();
            $globalwork=Globalwork::where('user_id',$this->user->id)->where('answer_check',false)->first();
            if ($globalwork!=null) {
                $chain->update(['globalwork_id'=>$globalwork->id]);
                $this->getGlobalworkQuestionData($chain->globalwork_id);
            }
            else {
                $this->text = 'Похоже что курс закончился, good JOB!';
                Telegram::triggerCommand('exit',$this->update);
            }
        }
        else {
            $this->text = 'Неправильный ответ';
        }
    }
    public function unknown():void
    {
        $this->text = 'i dont understand';
    }
    private function editing()
    {
        $this->text='Выберите из опций ниже';
        $this->buttons=[
            Keyboard::inlineButton([['text'=>'Создать курс','callback_data'=>'admin/course_create']]),
            Keyboard::inlineButton([['text'=>'Редактировать курс','callback_data'=>'admin/course_edit']]),
            Keyboard::inlineButton([['text'=>'Вернуться назад','callback_data'=>'Menu']])
        ];
        $this->keyboard=Keyboard::make(['inline_keyboard'=>$this->buttons])->inline();
    }
}
