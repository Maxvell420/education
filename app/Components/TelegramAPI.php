<?php

namespace App\Components;

use GuzzleHttp\Client;

class TelegramAPI
{
    public $client;
    public function __construct(){
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://api.telegram.org/',
            // You can set any number of default request options.
            'timeout'  => 4.0,
            'verify' =>false,
        ]);
    }

}
