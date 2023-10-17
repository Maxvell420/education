<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;
use App\Events\BotRebootEvent;
use App\Models\Url;
use App\Models\User;
use App\Services\botv2\CallbackQueryHandler;
use App\Services\botv2\InlineKeyboardResponse;
use App\Services\botv2\MessageHandler;
use Illuminate\Http\Response;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class BotControllerV2 extends Controller
{
    public function handle(): \Illuminate\Http\Response
    {
        $update = Telegram::commandsHandler(true);
        if ($update->isType('callback_query')) {
            $handler = new CallbackQueryHandler();
            $data = $handler->handle($update);
            if (isset($data['reply_markup'])){
                if ($data['reply_markup']->isInlineKeyboard()){
                    $handler->sendReply($data);
                    return response('OK', 200);
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
        return response('OK', 200);
    }
    public function rebootHandler():Response
    {
        Telegram::commandsHandler(true);
        return response('OK', 200);
    }
    private static function getNgrokUri(): string
    {
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
        Url::updateOrCreate(['url' => $response],['url' => $response]);
        return $response;
    }

    public static function setUpWebHook(): bool
    {
        $uri = self::getNgrokUri();
        return Telegram::setWebhook(
            [
                'url' => $uri . '/api/',
                'secret_token' => env('TELEGRAM_BOT_SECRET')
            ]);
    }

    public static function removeWebhook()
    {
        Telegram::removeWebhook();
    }
}
