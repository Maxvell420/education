<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;
use Illuminate\Support\Facades\Auth;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\{Course, Url, User};
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    public function handle(){
        $update=Telegram::commandsHandler(true);
        if  ($update->isType('callback_query'))   {
            switch ($update->callbackQuery->data){
                case 'available':
                    Telegram::triggerCommand('available courses',$update);
                    break;
                case 'joined':
                    Telegram::sendMessage(
                        ['chat_id' => $update->callbackQuery->message->chat->id,
                            'text' => 'it is newer for me']);
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
        $user=User::where('telegram_id',1955425357)->first();
        $joined_courses=Course::whereHas('globalworks',function ($query) use ($user)
        {
            $query->where('user_id',$user->id);
        })->get();
        $available_courses=Course::all()->diff($joined_courses)->where("course_complete","!=",null);
        $buttons=[];
        if ($available_courses->isNotEmpty()) {
            foreach ($available_courses as $course) {
                $buttons[] = Keyboard::inlineButton([['text' => $course->courseName, 'callback_data' => $course->id]]);
            }
        }
        $reply_markup=Keyboard::make(
            ['inline_keyboard'=>$buttons]);
        Telegram::sendMessage([
            'text' => 'bellow there are list of available courses for joining',
            'chat_id'=>$user->telegram_id,
            'reply_markup' => $reply_markup
        ]);
    }
}
