<?php

namespace App\Services\botv2;

use App\Components\TelegramAPI;
use App\Events\BotRebootEvent;
use App\Http\Controllers\BotControllerV2;
use App\Models\Chain;
use App\Models\Course;
use App\Models\Globalwork;
use App\Models\Question;
use App\Models\Url;
use App\Services\GlobalworkService;
use App\Services\QuestionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\UploadedFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class ReplyKeyboardResponse extends BotService
{
    private string $text;

    public function handle(string $message):string|bool
    {
        $chain = $this->user->chain()->first();
        try {
            if ($message == 'reboot')
            {
                if ($this->user->role_id>1){
                    $this->reboot();
                    return true;
                }
            }
            if (str_starts_with($message,'/course_open')) {
                $this->courseOpen(Course::find(substr($message,12)));
                return true;
            }
            if ($message == '/course_crea') {
                $this->courseCreateOrUpdatePreparation();
                return true;
            }
            if (str_starts_with($message,'/course_edit')) {
                $this->courseCreateOrUpdatePreparation($message);
                return true;
            }
            if(str_starts_with($message,'/globalwork')) {
                return $this->getQuestionData(substr($message,11));
            }
            if (str_starts_with($message,'/question_edit')) {
                    $question = Question::find(substr($message,14));
                    $this->questionUpdateOrCreatePreparation($question);
                    return true;
            }
            if (str_starts_with($message,'/question_create')) {
                $course = Course::find(substr($message,16));
                $this->questionUpdateOrCreatePreparation($course);
                return true;
            }
            if ($chain) {
                if ($chain->admin) {
                    if ($chain->course_id and !$chain->question_id) {
                        return $this->courseMaking($chain,$message);
                    }
                    if ($chain->question_id){
                        return $this->questionMaking($message);
                    }
//                    Обработка цепи админа на создание и редактирование курсов/вопросов.
                } else {
                    $globalworkService = new GlobalworkService(Globalwork::find($chain->globalwork_id));
                    $globalworkService->GlobalworkUpdate($message);
                    return $this->user_answer_check($globalworkService->answerCheck(),$chain);
                }
            }
//            Здесь будет проверка на цепь, если цепи нет то команды на ее создание
        } catch (\Exception $e) {
            return $this->text = $e->getMessage();
        }
        return false;
    }
    private function reboot()
    {
        BotControllerV2::removeWebhook();
        sleep(2);
        $uri = Url::first()->url;
        Telegram::setWebhook(
            ['url'=>$uri.'/api/rebootHandler',
                'secret_token'=>env('TELEGRAM_BOT_SECRET')
            ]);
        event(new BotRebootEvent($this->user));
        $this->text = 'Бот будет перезапущен через 15 секунд';
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
    private function userChainCreate(Model $model):Chain|Model
    {
        $chain = $this->user->chain();
        $chain->delete();
        return $chain->create([strtolower(class_basename($model)).'_id'=>$model->id]);
    }
    private function getQuestionData(int $globalwork_id)
    {
        $globalwork=Globalwork::find($globalwork_id);
        $globalworkService= new GlobalworkService($globalwork);
        $this->userChainCreate($globalwork);
        $data=$globalworkService->GlobalworkShowData();
        $question = $data['Название'];
        $photo=$data['file'];
        if ($photo!=null) {
            Telegram::sendPhoto(['chat_id'=>$this->user->telegram_id,'photo'=>$photo->file_id]);
        }
        $this->buttons[]= Keyboard::button([['text'=>'/exit']]);
        $this->buttons[]= Keyboard::button([['text'=>$question->answer_4]]);
        $this->buttons[]= Keyboard::button([['text'=>$question->answer_3]]);
        $this->buttons[]= Keyboard::button([['text'=>$question->answer_2]]);
        $this->buttons[]= Keyboard::button([['text'=>$question->answer_1]]);
        $this->keyboard=Keyboard::make(['keyboard'=>array_reverse($this->buttons),'resize_keyboard'=>true]);
        return $this->text=$question->problem;
    }
    private function user_answer_check(bool $flag, $chain)
    {
        if ($flag) {
            Telegram::sendMessage(['chat_id'=>$this->user->telegram_id,'text'=>'Правильный ответ']);
            $globalwork=Globalwork::where('user_id',$this->user->id)->where('answer_check',false)->first();
            if ($globalwork!=null) {
                $chain->update(['globalwork_id'=>$globalwork->id]);
                $this->getQuestionData($chain->globalwork_id);
            }
            else {
                $this->text = 'Похоже что курс закончился, good JOB!';
                $this->keyboard=Keyboard::remove(['remove_keyboard'=>true]);
                $chain->delete();
            }
        }
        else {
            $this->text = 'Неправильный ответ';
        }
        return $this->text;
    }

    /**
     * @param string|null $message
     * @return void
     * При создании курса в цепь в поле 'course_id' добавляется -1, при редактировании id курса.
     * После этого проверяется какое id у курса в цепи, и если id = -1, то отправляются инструкции по созданию курса, если любое число >0 ,
     * то отправляется клавиатура с тем, что Админ хочет отредактировать.
     * P.S. для упрощения кода в случаях когда Админ начинает редактировать/создавать курс , но цепь еще существует , цепь удаляется и создается заного.
     */
    private function courseCreateOrUpdatePreparation(string $message=null):void
    {
        $chain = $this->user->chain();
        $chain->delete();
        $id = substr($message, 12) ?: -1;
        $chain=$chain->create(['admin'=>true,'course_id'=>$id]);
        if ($chain->course_id>0) {
            $course = $chain->course()->first();
            $chain->variable_1 = $course->courseName;
            $chain->variable_2 = $course->course_info;
            $chain->save();
            $this->courseMaking($chain,$message);
        } else {
            $this->buttons[]= Keyboard::button([['text'=>'/exit']]);
            $this->keyboard = Keyboard::make(['keyboard'=>$this->buttons]);
            $this->text='Приступаем к созданию курса, отправьте мне название создаваемого курса';
        }
    }
    private function courseMaking(Chain|model $chain,string $message = null):string
    {
        $message =$this->courseEditingHandle($chain,$message);
        if ($message) {
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
        }
        if ($chain->variable_1  and $chain->variable_2 and $chain->wasChanged()) {
            $this->buttons = [
                Keyboard::button([['text' => 'Сохранить курс']]),
                Keyboard::button([['text' => 'Редактировать']]),
                Keyboard::button([['text'=>'Посмотреть информацию курса']]),
                Keyboard::button([['text'=>'/exit']])
            ];
            $this->keyboard = Keyboard::make(['keyboard' => $this->buttons,'one_time_keyboard'=>true]);
        }
        if (!$message) {
            if (!$chain->variable_1) {
                return $this->text='Пришли название курса';
            }
            if (!$chain->variable_1 and !$chain->variable_2) {
                return $this->text='Название курса: ' . $chain->variable_1.'. Пришли мне описание курса';
            }
            if (!$chain->variable_2) {
                return $this->text='Пришли мне описание о курсе';
            }
        }
        if (!isset($this->text)){
            return $this->text = 'Воспользуйтесь клавиатурой ниже';
        }
        return $this->text;
    }
    private function courseEditingHandle(Chain $chain,string $message=null):?string
    {
        switch ($message){
            case str_starts_with($message,'/course_edit'):
                return null;
            case 'Название курса':
                $chain->update(['variable_1'=>null]);
                return null;
            case 'Описание курса':
                $chain->update(['variable_2'=>null]);
                return null;
            case 'Сохранить курс':
                Course::query()->updateOrCreate(['id'=>$chain->course_id],['courseName'=>$chain->variable_1,'course_info'=>$chain->variable_2]);
                $this->text='Изменения курса сохранены';
                $this->keyboard=Keyboard::remove(['remove_keyboard'=>true]);
                $chain->delete();
                return null;
            case 'Посмотреть информацию курса':
                $this->text = 'Название курса:'.$chain->variable_1. '. Описание курса:'.$chain->variable_2;
                return null;
            case 'Редактировать':
                $this->text='Что именно вы хотите редактировать?';
                $this->buttons=
                    [
                        Keyboard::button([['text'=>'Название курса']]),
                        Keyboard::button([['text'=>'Описание курса']]),
                        Keyboard::button([['text'=>'Сохранить курс']]),
                    ];
                $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons,'one_time_keyboard'=>true]);
                return null;
            default:
                return $message;
        }
    }
    private function questionUpdateOrCreatePreparation(Model|null $model):void
    {
        $this->user->chain()->delete();
        $chain = $this->userChainCreate($model);
        $chain->admin = true;
        if ($chain->question_id){
            $question = $chain->question()->first();
            $chain->variable_1 = $question->title;
            $chain->variable_2 = $question->problem;
            $chain->variable_3 = $question->answer_1;
            $chain->variable_4 = $question->answer_2;
            $chain->variable_5 = $question->answer_3;
            $chain->variable_6 = $question->answer_4;
            $chain->variable_7 = substr($question->correct_answer,7);
            $chain->variable_8 = $question->downloads()->first()->file_id??null;
            $chain->save();
            $this->questionMaking();
        } else {
            $chain->question_id = -1;
            $chain->save();
            $this->buttons[] = Keyboard::button([['text'=>'/exit']]);
            $this->keyboard = Keyboard::make(['keyboard'=>$this->buttons]);
            $this->text='Приступаем к созданию вопроса, отправь мне название вопроса';
        }
    }
    private function questionMaking(string $message=null)
    {
        $chain = $this->user->chain()->first();
        $array = array_diff_key(($chain->toArray()),array_flip([
            'id', 'admin','globalwork_id','course_id','question_id','variable_8','user_id'
        ]));
        $message = $this->questionCommandsHandle($chain,$message);
        if ($message){
            foreach ($array as $item => $value){
                if (!$chain->$item){
                    $chain->$item = $message;
                    $chain->save();
                    $this->text = 'Принято';
                    break;
                }
            }
        }
        if (!isset($this->text)) {
            $this->text = 'Все данные для создания вопроса уже есть';
        }
        if (!$chain->variable_1) {
                return $this->text=$this->text.'. Пришли мне тему вопроса';
        }
        elseif (!$chain->variable_2) {
                return $this->text=$this->text.'. Пришли мне проблему вопроса';
        }
        elseif (!$chain->variable_3) {
                return $this->text=$this->text.'. Пришли мне вариант ответа 1';
        }
        elseif (!$chain->variable_4) {
                return $this->text=$this->text.'. Пришли мне вариант ответа 2';
        }
        elseif (!$chain->variable_5) {
                return $this->text=$this->text.'. Пришли мне вариант ответа 3';
        }
        elseif (!$chain->variable_6) {
                return $this->text=$this->text.'. Пришли мне вариант ответа 4';
        }
        elseif (!$chain->variable_7) {
                return $this->text=$this->text.'. Пришли мне номер правильного варианта ответа';
        }
        elseif (!$chain->variable_8 and $this->user->chain()->first()!=null) {
                $this->text=$this->text.'. Можешь прислать мне изображение к вопросу';
        }
        if (!isset($this->keyboard)){
            $this->questionCreateOrUpdateMenu($chain);
        }
        return $this->text;
    }
    private function questionCommandsHandle(Chain|HasOne $chain,string $message=null):?string
    {
        switch ($message) {
            case 'Посмотреть данные вопроса':
                $this->questionChainCheck($chain);
                return null;
            case 'Редактировать':
                $this->chainQuestionEditMenu($chain);
                break;
            case 'Тему вопроса':
                $chain->update(['variable_1'=>null]);
                break;
            case 'Удалить вопрос' and $chain->question_id>0:
                $question = $chain->Question()->first();
                $this->keyboard = Keyboard::remove(['remove_keyboard' => true,]);
                $chain->delete();
                $this->keyboard = Keyboard::remove(['remove_keyboard' => true,]);
                $service = new QuestionService();
                $service->questionDelete($question);
                $this->text = 'Вопрос был удален';
                break;
            case 'Сохранить вопрос в курс':
                $request=$this->chainQuestionRequest($chain);
                $service = new QuestionService();
                if ($chain['question_id']>0) {
                    $question = $chain->question()->first();
                    $course=$question->course()->first();
                    $chain['course_id'] = $course->id;
                }
                else {
                    $course = $chain->course()->first();
                }
                try {
                    if (isset($question)) {
                        $service->questionUpdate($request,$question);
                        $service->fileUpdate($request,$question);
                    } else {
                        $service->questionCreate($request,$course);
                    }
                }
                catch (\Exception $e) {
                    $this->text=$e->getMessage();
                }
                if (!isset($this->text)) {
                    $this->text='Изменения вопроса сохранены';
                    $this->keyboard = Keyboard::remove(['remove_keyboard' => true,]);
                    $chain->delete();
                }
                return null;
            case 'Проблему вопроса':
                $chain->update(['variable_2'=>null]);
                break;
            case 'Вариант ответа 1':
                $chain->update(['variable_3'=>null]);
                break;
            case 'Вариант ответа 2':
                $chain->update(['variable_4'=>null]);
                break;
            case 'Вариант ответа 3':
                $chain->update(['variable_5'=>null]);
                break;
            case 'Вариант ответа 4':
                $chain->update(['variable_6'=>null]);
                break;
            case 'Номер правильного варианта':
                $chain->update(['variable_7'=>null]);
                break;
            case 'Изображение к вопросу':
                $chain->update(['variable_8'=>null]);
                break;
        }
        if ($chain->wasChanged()) {
            $this->text='Принято';
            return null;
        }
        else return $message;
    }
    private function questionChainCheck(Chain $chain):void
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
    private function chainQuestionEditMenu():void
    {
        $this->text = 'Что вы хотите редактировать?';

        $buttonTexts = [
            'Тему вопроса',
            'Проблему вопроса',
            'Вариант ответа 1',
            'Вариант ответа 2',
            'Вариант ответа 3',
            'Вариант ответа 4',
            'Номер правильного варианта',
            'Изображение к вопросу',
            '/exit'
        ];
        foreach ($buttonTexts as $text) {
            $this->buttons[] = Keyboard::button([['text' => $text]]);
        }

        $this->keyboard = Keyboard::make(['keyboard' => $this->buttons, 'one_time_keyboard' => true]);
    }
    private function questionCreateOrUpdateMenu(Chain $chain): void
    {
        $this->buttons[]=Keyboard::button([['text'=>'Сохранить вопрос в курс']]);
        $this->buttons[]= Keyboard::button([['text'=>'Посмотреть данные вопроса']]);
        $this->buttons[]= Keyboard::button([['text'=>'Редактировать']]);
        if ($chain->question_id>0) {
            $this->buttons[] = Keyboard::button([['text'=>'Удалить вопрос']]);;
        }
        $this->buttons[]= Keyboard::button([['text'=>'/exit']]);
        $this->keyboard=Keyboard::make(['keyboard'=>$this->buttons,'one_time_keyboard'=>true]);
    }

    private function chainQuestionRequest(Chain $chain):\Illuminate\Http\Request
    {
        $requestData = [
            'question_id' => $chain->question_id,
            'title' => $chain->variable_1,
            'problem' => $chain->variable_2,
            'answer_1' => $chain->variable_3,
            'answer_2' => $chain->variable_4,
            'answer_3' => $chain->variable_5,
            'answer_4' => $chain->variable_6,
            'correct_answer' => $chain->variable_7,
        ];

        if (isset($chain->variable_8)) {
            $requestData['file'] = $this->getPic($chain);
        }

        $request = request()->merge($requestData);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        return $request;
    }
    private function courseOpen(Course $course):void
    {
        $course->update(['course_complete'=>true]);
        $this->text='Курс был открыт для записи';
    }

    private function getPic(Chain $chain):UploadedFile
    {
        $path = Telegram::getFile(['file_id'=>$chain->variable_8])->filePath;
        $tempFilePath = tempnam(sys_get_temp_dir(), 'prefix');
        $import = new TelegramAPI();
        $response = $import->client->request('GET','file/bot'.env('TELEGRAM_BOT_TOKEN').'/'.$path);
        $fileContent = $response->getBody()->getContents();
        file_put_contents($tempFilePath, $fileContent);
        return new UploadedFile($tempFilePath ,'Question:'.$chain->variable_1.'.jpg','image/jpeg',null,true);
    }

}
