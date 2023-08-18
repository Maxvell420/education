<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class CurrentExamine
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $examine=User::find(auth()->user()->id)->currectExamine()->first();
        if (isset($examine->id)){
            $request->request->add(['examine'=>$examine->id]);
            return $next($request);
        } else  {
            $request->request->add(['examine'=>null]);
        }
        return $next($request);
    }
}
