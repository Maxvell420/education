<?php

namespace App\Services\botv2;

use App\Services\UserService;
use Telegram\Bot\Keyboard\Keyboard;

abstract class BotService extends UserService
{
    protected array $buttons;
    protected Keyboard $keyboard;
    abstract public function handle(string $message);
    abstract protected function replyPreparation();
}
