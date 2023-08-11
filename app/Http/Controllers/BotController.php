<?php

namespace App\Http\Controllers;

use App\components\NgrokAPI;
use App\components\testAPI;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
class BotController extends Controller
{
    public function getNgrokUri(){
        $import=new NgrokAPI();
        $response=$import->client->request('GET','/tunnels',['headers'=>[
            'Authorization'=>"Bearer"." ".env('NGROK_API_KEY'),
            'Ngrok-Version'=>2
        ]]);
        foreach(json_decode( $response->getBody(),true)as $key) {
            return $key[0]['public_url'];}
    }
    public function setUpNgrok(){
        $response=Telegram::setWebhook(['url'=>$this->getNgrokUri()]);
        return 0;
    }
//    public function test(){
//        $inport = new testAPI();
//        $response=$inport->client->request('get','posts');
//        foreach (json_decode($response->getBody()->getContents(),true) as $key){
//            return $key;
//        };
//    }
}
