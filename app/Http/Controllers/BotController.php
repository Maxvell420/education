<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;
use App\Services\GlobalworkService;
use Illuminate\Support\Facades\Auth;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\{Chain, Course, Globalwork, Question, Url, User};
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    public function handle(){
        $update=Telegram::commandsHandler(true);
        if  ($update->isType('callback_query'))   {
            switch (substr($update->callbackQuery->data,0,1)==0){
                case 0:
                    if (substr($update->callbackQuery->data,1)==0) {
                        Telegram::triggerCommand('availableCourses',$update);
                    }
                    elseif (substr($update->callbackQuery->data,1)==1) {
                        Telegram::triggerCommand('joinedCourses',$update);
                    }
                    else {
                        return response('not found',404);
                    }
                    break;
                case 1:
                    Telegram::triggerCommand('CourseShow',$update);
                    break;
                case 2:
//                    'join course = 0' or 'question Show >1'
                    break;
                case 3:
//                    'answers for question переделать вопросы чтобы были 4 варианта ответа и правильный вариант отдельная колонка ->
//                    из-за этого надо переделывать все вопросы'
                    break;
            }
        }
//    event(new \App\Events\BotMessageEvent($update));
        return response('OK',200);
    }
    public function getNgrokUri():string{
        $import=new NgrokAPI();
        $response=$import->client->request('GET','/tunnels',
            ['headers'=>[
            'Authorization'=>"Bearer"." ".env('NGROK_API_KEY'),
            'Ngrok-Version'=>2
        ]]);
        foreach(json_decode( $response->getBody(),true)as $key) {
            $response=$key[0]['public_url'];
            break;}
        Url::updateOrCreate(['url'=>$response]);
        return $response;
    }
    public function setUpWebHook():bool{
        $uri=$this->getNgrokUri();
        return Telegram::setWebhook(
            [
                    'url'=>$uri.'/api/',
                    'secret_token'=>env('TELEGRAM_BOT_SECRET')
            ]);
    }
    public function removeWebhook(){
        Telegram::removeWebhook();
    }
    public function getMe(){
        return Telegram::getMe();
    }
    public function getUpdates(){
        return Telegram::getUpdates();
    }
    public function delete()
    {
        $user=Auth::user();
        $joined_courses=Course::whereHas('globalworks',function ($query) use ($user)
        {
            $query->where('user_id',1);
        })->get(['id','courseName']);
        if ($joined_courses->isNotEmpty()) {
            $keyboard[] = Keyboard::inlineButton([['text' => 'joined courses','callback_data'=>'joined']]);
        }
        dump($joined_courses);
    }
    public function test(){

//        $GlobalworkService = new GlobalworkService($globalwork);
//        $data =$GlobalworkService->GlobalworkShowData();
        $data=collect([$globalwork->course()->first()]);
        return $data;
        }
}
