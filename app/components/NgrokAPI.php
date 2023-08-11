<?php

namespace App\components;

use GuzzleHttp\Client;

class NgrokAPI
{
    public $client;
    public function __construct(){
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://api.ngrok.com',
            // You can set any number of default request options.
            'timeout'  => 2.0,
            'verify' =>false,
        ]);
    }

}
