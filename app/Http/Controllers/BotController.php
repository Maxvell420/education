<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;

use App\Http\Requests\AnswersRequest;
use App\Services\BotService;
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
//        Telegram::triggerCommand('start',$update);
        if (!isset($update->message->entities)) {
                $bot = new BotService($update);
                $bot->handle();
        }
        return response('OK', 200);
    }
    public function delete(Request $request)
    {
        $updates = Telegram::getUpdates();
        foreach ($updates as $update) {
            $bot = new BotService($update);
            Telegram::triggerCommand('menu',$update);
//            $bot->sendMessage();
            $bot->handle();
        }
        return response('OK', 200);
    }

    public function test()
    {
        Downloads::create(['question_id'=>3,'file_id'=>1235,'course_id'=>1]);
    }
    public function getNgrokUri(): string
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
        Url::updateOrCreate(['url' => $response]);
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
