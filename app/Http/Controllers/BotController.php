<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        if (http_response_code()==200){
            return "yes";
        }
        return Telegram::getMe();
    }
    public function getUpdates(){
        return Telegram::getUpdates();
    }
    public function test(){
        $url=Url::first('url');
        return 'yes';
    }
}
