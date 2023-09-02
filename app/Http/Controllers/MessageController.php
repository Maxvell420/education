<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\{Globalwork,Question};
use App\Services\MessageService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class MessageController extends Controller
{
    public function Store(MessageRequest $request,Globalwork $globalworks):RedirectResponse
    {
        $messageService = new MessageService();
        $messageService->messageStore($request,$globalworks);
        return Redirect::back()->with("message","your message was successfully sent");
    }
    public function Error(Question $question,Globalwork $globalworks):Renderable
    {
        return view("questions.messagesForm",["question"=>$question,"globalworks"=>$globalworks]);
    }
    public function Show(Globalwork $globalworks){
        $messageService = new MessageService();
        $data=$messageService->messageShow($globalworks);
        return view("messages.show",['data'=>$data]);
    }

}
