<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Telegram\Bot\Keyboard\Keyboard;
use App\Models\{Url,User};
use Illuminate\Support\Facades\Hash;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
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
    public function getBotId():int
    {
        return Telegram::getMe()->id;
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
    public function test(){
        switch (User::where('name','velichko')->first()){
            case !null:
                $text = 'You already started';
                break;
            case null:
                User::create(['name'=>'Mavelich','telegram_id'=>12315151]);
                $text='Hey, there! Welcome to our bot!';
        }
        $reply_markup = Keyboard::make([
            'keyboard' => [
                    Keyboard::button([['text'=>'available courses','request_contact'=>true],
                    Keyboard::button(['text'=>'joined courses'])
                    ])],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,]);
        Telegram::sendMessage([
            'text' => $text,
            'chat_id'=>1955425357,
            'reply_markup' => $reply_markup
        ]);
    }
}
