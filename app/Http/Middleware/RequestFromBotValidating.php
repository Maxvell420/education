<?php

namespace App\Http\Middleware;

use App\Models\Url;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class RequestFromBotValidating
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $url=Url::first('url');
        $response=Http::asForm()->post($url->url.'/oauth/token',['grant_type'=>'client_credentials',
            'client_id'=>2,
            'client_secret'=>$request->headers->get('X-Telegram-Bot-Api-Secret-Token'),
            'scope']);
        $request->headers->set('Authorization',json_decode($response)->token_type." ".json_decode($response)->access_token);
        $request->headers->set('Accept','application/json');
        return $next($request);
    }
}
