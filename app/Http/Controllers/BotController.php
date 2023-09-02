<?php

namespace App\Http\Controllers;

use App\Components\NgrokAPI;

use App\Http\Requests\AnswersRequest;
use App\Services\BotService;
use App\Services\GlobalworkService;
use App\Models\{Chain, Course, Globalwork, Question, Url, User};
use Illuminate\Support\Facades\Request;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use function PHPUnit\Framework\isEmpty;

class BotController extends Controller
{
    public function handle()
    {
        $update = Telegram::commandsHandler(true);
        $bot = new BotService($update);
        $chain = $bot->user->chain()->first();
        if ($chain!=null) {
            if ($globalwork_id=$chain->globalwork_id??null) {
                $globalworkService = new GlobalworkService(Globalwork::find($globalwork_id));
                $globalworkService->GlobalworkUpdate($bot->user->id,$update->message->text);
                $bot->user_answer_check($globalworkService->answerCheck(),$chain);
                $bot->sendMessage();
            }
        }
        if ($chain == null and $update->isType('message') and !isset($update->message->entities)){
            $bot->unknown();
            $bot->sendMessage();
        }
        if ($update->isType('callback_query')) {
            switch (substr($update->callbackQuery->data, 0, 1)) {
                case 1:
                    $course = Course::find(substr($update->callbackQuery->data, 2));
                    $bot->showCourse($course);
                    break;
                case 2:
                    $course = Course::find(substr($update->callbackQuery->data, 2));
                    $bot->registerUserInCourse($course);
                    break;
                case 3:
                    $bot = new BotService($update);
                    $bot->getGlobalworkQuestionData(substr($bot->data, 2));
                    break;
            }
            $bot->sendMessage();
        }
        return response('OK', 200);
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

    public function delete(Request $request)
    {
        $updates = Telegram::getUpdates();
        foreach ($updates as $update) {
            Telegram::triggerCommand('start',$update);
//            $bot = new BotService($update);
//            $chain = $bot->user->chain()->first();
//            if ($chain != null) {
//                if ($globalwork_id = $chain->globalwork_id ?? null) {
//                    $globalworkService = new GlobalworkService(Globalwork::find($globalwork_id));
//                    $globalworkService->GlobalworkUpdate($bot->user->id, $update->message->text);
//                    $bot->user_answer_check($globalworkService->answerCheck(), $chain);
//                    $bot->sendMessage();
//                }
//            }
//            if ($chain == null and $update->isType('message') and !isset($update->message->entities)) {
//                $bot->unknown();
//                $bot->sendMessage();
//            }
//            if ($update->isType('callback_query')) {
//                switch (substr($update->callbackQuery->data, 0, 1)) {
//                    case 1:
//                        $course = Course::find(substr($update->callbackQuery->data, 2));
//                        $bot->showCourse($course);
//                        break;
//                    case 2:
//                        $course = Course::find(substr($update->callbackQuery->data, 2));
//                        $bot->registerUserInCourse($course);
//                        break;
//                    case 3:
//                        $bot = new BotService($update);
//                        $bot->getGlobalworkQuestionData(substr($bot->data, 2));
//                        break;
//                }
//                $bot->sendMessage();
//            }
//            Telegram::triggerCommand('start',$update);
            return response('OK', 200);
        }
    }
    public function test()
    {
        Telegram::deleteMessage(['chat_id'=>960717582,'message_id'=>1479]);
    }
}
