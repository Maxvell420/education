<?php

namespace App\Http\Middleware;

use App\Models\Url;
use App\Models\User;
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
        if ($request->headers->get('X-Telegram-Bot-Api-Secret-Token')==env('TELEGRAM_BOT_SECRET')){
            $user=User::find(2);
            if ($user->token==null) {
                $url=Url::first('url');
                $response = Http::asForm()->post($url->url . '/oauth/token', ['grant_type' => 'client_credentials',
                    'client_id' => 2,
                    'client_secret' => $request->headers->get('X-Telegram-Bot-Api-Secret-Token'),
                    'scope']);
                $user->token=json_decode($response)->token_type." ".json_decode($response)->access_token;
                $user->save();
                $response=$user->token;
            }
            else $response=$user->token;
        }
        else abort(403);
        $request->headers->set('Authorization',$response);
        $request->headers->set('Accept','application/json');
        return $next($request);
    }
}
