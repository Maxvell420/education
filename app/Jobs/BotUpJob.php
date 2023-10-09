<?php

namespace App\Jobs;

use App\Http\Controllers\BotControllerV2;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;

class BotUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private User $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        BotControllerV2::removeWebhook();
        sleep(2);
        BotControllerV2::setUpWebHook();
        sleep(2);
        Telegram::sendMessage(
            ['chat_id'=>$this->user->telegram_id,
                'text'=>'Бот был перезапущен'
            ]);
    }
}
