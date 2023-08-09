<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class CurrectExamine
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $examine=User::find(auth()->user()->id)->currectExamine()->first("id");
        if (isset($examine->id)){
            $course=$request->exam->course()->first();
            return Redirect::route("question/show",[$course])->with("warning","finish this examine,before start a new one");
        }
        else return $next($request);
    }
}
