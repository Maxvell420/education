<?php

namespace App\Jobs;

use App\Http\Controllers\BotControllerV2;
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
    private Update $update;
    public function __construct(Update $update)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        BotControllerV2::removeWebhook();
        BotControllerV2::setUpWebHook();
        Telegram::sendMessage(
            ['chat_id'=>$this->update->message->from->id,
                'text'=>'Бот был перезапущен'
            ]);
    }
}
