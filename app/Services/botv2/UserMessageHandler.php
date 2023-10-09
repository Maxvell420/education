<?php

namespace App\Services\botv2;

use App\Models\Chain;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class UserMessageHandler extends BotService
{
    public function handle(string $update):array
    {
//        Проверка цепи?
        $chain = $this->user->chain()->first();
        $mainCommand =
        $command = $this->QuestionCommandsHandle($update,$chain);
        if ($command) {
            return $this->messageSendPreparation($update);
        }
        else {
            return [];
        }
    }

    private function QuestionCommandsHandle(string $message,Chain $chain):?string
    {
        switch ($message) {
            case 'Посмотреть данные вопроса':
                $this->chainCheck($chain);
                return null;
            case 'Редактировать':
                $this->chainQuestionEditMenu();
                return null;
            case 'Тему вопроса':
                $chain->update(['variable_1'=>null]);
                return null;
            case 'Проблему вопроса':
                $chain->update(['variable_2'=>null]);
                return null;
            case 'Вариант ответа 1':
                $chain->update(['variable_3'=>null]);
                return null;
            case 'Вариант ответа 2':
                $chain->update(['variable_4'=>null]);
                return null;
            case 'Вариант ответа 3':
                $chain->update(['variable_5'=>null]);
                return null;
            case 'Вариант ответа 4':
                $chain->update(['variable_6'=>null]);
                return null;
            case 'Номер правильного варианта':
                $chain->update(['variable_7'=>null]);
                return null;
            case 'Изображение к вопросу':
                $chain->update(['variable_8'=>null]);
                return null;
        }
        if ($chain->wasChanged()) {
            $this->text='Принято';
            return null;
        }
        else return $message;
    }

    private function chainCheck(Chain $chain):void
    {
        $this->text='Тема вопроса:'.$chain->variable_1;
        $this->text=$this->text.'. Проблема вопроса:'.$chain->variable_2;
        $this->text=$this->text.'. Вариант ответа 1:'.$chain->variable_3;
        $this->text=$this->text.'. Вариант ответа 2:'.$chain->variable_4;
        $this->text=$this->text.'. Вариант ответа 3:'.$chain->variable_5;
        $this->text=$this->text.'. Вариант ответа 4:'.$chain->variable_6;
        $this->text=$this->text.'. Номер правильного варианта:'.$chain->variable_7;
        if (isset($chain->variable_8)){
            Telegram::sendPhoto(['chat_id'=>$this->user->telegram_id,'photo'=>$chain->variable_8]);
        }
    }

    private function chainQuestionEditMenu()
    {
        $this->text='Что вы хотите редактировать?';
        $this->buttons[]=Keyboard::button([['text'=>'Тему вопроса']]);
        $this->buttons[]=Keyboard::button([['text'=>'Проблему вопроса']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 1']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 2']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 3']]);
        $this->buttons[]=Keyboard::button([['text'=>'Вариант ответа 4']]);
        $this->buttons[]=Keyboard::button([['text'=>'Номер правильного варианта']]);
        $this->buttons[]=Keyboard::button([['text'=>'Изображение к вопросу']]);
        return null;
    }
    protected function messageSendPreparation(Update $update):array
    {
        $message=['text'=>$this->text,'chat_id'=>$update->message->from->id];
        if (isset($this->keyboard)){
            $message[]=['reply_markup'=>$this->keyboard];
        }
        return $message;
    }

    protected function sendReply()
    {
        // TODO: Implement sendReply() method.
    }

    protected function replyPreparation()
    {
        // TODO: Implement replyPreparation() method.
    }
}
