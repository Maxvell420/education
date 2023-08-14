<?php

namespace App\Components\Telegram\Commands;

use App\Models\Bot;
use Telegram\Bot\Commands\Command;
class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';
    public function handle()
    {
        $this->replyWithMessage([
            'text' => 'Hey, there! Welcome to our bot!',
        ]);
    }
}