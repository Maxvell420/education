<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;

use App\Events\BotRebootEvent;
use App\Http\Requests\AnswersRequest;
use App\Services\BotService;
use App\Services\botv2\CallbackQueryHandler;
use App\Services\botv2\MessageHandler;
use App\Services\GlobalworkService;
use App\Models\{Chain, Course, Downloads, Globalwork, Question, Url, User};
use Illuminate\Support\Facades\Request;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use function PHPUnit\Framework\isEmpty;

class BotController extends Controller
{
    public function handle()
    {
        $update = Telegram::commandsHandler(true);
        if (!isset($update->message->entities)) {
                $bot = new BotService($update);
                $bot->handle();
        }
        return response('OK', 200);
    }
    public function test()
    {
       $updates = Telegram::getUpdates();
       foreach ($updates as $update){
           if ($update->isType('callback_query')) {
               $handler = new CallbackQueryHandler();
               $data = $handler->handle($update);
               if (isset($data['reply_markup'])){
                   if ($data['reply_markup']->isInlineKeyboard()){
                       $handler->sendReply($data);
                       break;
                   }
               }
                   $message = new MessageHandler();
                   $message->sendReply($data);
           }
           elseif ($update->isType('message')) {
               $handler = new MessageHandler();
               $data = $handler->handle($update);
               $handler->sendReply($data);
           }
      }
        return response('OK', 200);
    }
    public function getNgrokUri(): string
    {
        $url = Url::first();
        $import = new NgrokAPI();
        $response = $import->client->request('GET', '/tunnels',
            ['headers' => [
                'Authorization' => "Bearer" . " " . env('NGROK_API_KEY'),
                'Ngrok-Version' => 2
            ]]);
        foreach (json_decode($response->getBody(), true) as $key) {
            $response = $key[0]['public_url'];
            break;
        }
        if ($url->url != $response){
            $url->update(['url'=>$response]);
        }
        return $response;
    }

    public function setUpWebHook(): bool
    {
        $uri = $this->getNgrokUri();
        return Telegram::setWebhook(
            [
                'url' => $uri . '/api/',
                'secret_token' => env('TELEGRAM_BOT_SECRET')
            ]);
    }

    public function removeWebhook()
    {
        Telegram::removeWebhook();
    }

    public function getMe()
    {
        return Telegram::getMe();
    }

    public function getUpdates()
    {
        return Telegram::getUpdates();
    }
}
