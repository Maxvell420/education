<?php

namespace App\Services\botv2;

use App\Models\User;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class CallbackQueryHandler implements iHandle
{
    public function handle(Update $update):array
    {
        try {
            $user = User::where('telegram_id',$update->callbackQuery->message->chat->id)->first();
            $request = new InlineKeyboardResponse($user);
            $exception = $request->handle($update->callbackQuery->data);
            if ($exception){
                $data = $request->replyPreparation();
                $data['message_id'] = $update->callbackQuery->message->messageId;
                return $data;
            }
            elseif (is_string($exception) and $user->role_id==2){
                Telegram::sendMessage(['chat_id'=>$user->telegram_id,'text'=>$exception]);
            }
            $request = new ReplyKeyboardResponse($user);
            $request->handle($update->callbackQuery->data);
            return $request->replyPreparation();
        } catch (\Exception $e){
            return ['text'=>$e->getMessage(),'chat_id'=>$update->callbackQuery->from->id];
        }
    }

    public function sendReply(Array $data): void
    {
        Telegram::editMessageReplyMarkup($data);
    }
}
