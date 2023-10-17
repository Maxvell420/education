<?php

namespace App\Components\Telegram\Commands;

use App\Models\User;
use App\Services\BotService;
use App\Services\botv2\MessageHandler;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class MenuCommand extends Command
{
    protected string $name = 'menu';
    protected string $description = 'Присылает главное меню';
    public function handle(): void
    {
        $update = $this->update;
        $request = new MessageHandler();
        $request->handle($update);
    }
}
