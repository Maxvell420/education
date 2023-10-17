<?php

namespace App\Services\botv2;

use App\Models\User;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class MessageHandler implements iHandle
{
//Подумать насчет картинок? отправлять вмеcто текста сообщения id картинки в обработчик
    public function handle(Update $update):array
    {
        $user = User::where('telegram_id',$update->message->from->id)->first();
        try {
            if (isset($update->message->photo)) {
                $chain = $user->chain()->first();
                if ($chain->admin and $chain->question_id){
                    foreach ($update->message->photo->reverse() as $photo){
                        $file_id=$photo->file_id;
                        $chain->update(['variable_8'=>$file_id]);
                        return ['text'=>'Изображение было сохранено',
                            'chat_id'=>$update->message->from->id];
                    }
                }
            }
            if (isset($update->message->entities)) {
                $handler =  new InlineKeyboardResponse($user);
                $handler->handle($update->message->text);
                $data = $handler->replyPreparation();
            }
            if (!isset($data)) {
                $message = new ReplyKeyboardResponse($user);
                $message->handle($update->message->text);
                $data = $message->replyPreparation();
            }
            return $data;
        }
        catch (\Exception $e){
            return ['text'=>$e->getMessage(),'chat_id'=>$user->telegram_id];
        }
    }

    public function sendReply(array $data)
    {
        Telegram::sendMessage($data);
    }

}
