<?php

namespace App\Services\botv2;

use Telegram\Bot\Objects\Update;

interface iHandle
{
    public function handle(Update $update);
    public function sendReply(Array $data);
}
