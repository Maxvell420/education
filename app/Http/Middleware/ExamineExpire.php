<?php

namespace App\Http\Middleware;

use App\Models\Examine;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamineExpire
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $examine=User::find(auth()->user()->id)->currectExamine()->first("id");
            if(!isset($examine->id)){
                $exam=$request->course->exams()->where("exam_closure",false)->first();
            return redirect()->route("examine.results",[$exam]);
            }
            else return $next($request);
    }
}
