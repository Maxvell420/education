<?php

namespace App\Services;

use App\Http\Requests\MessageRequest;
use App\Models\Chat_message;
use App\Models\Globalwork;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;

class MessageService
{
    public function messageStore(MessageRequest $request,Globalwork $globalworks){
        $message = new Chat_message();
        if (auth()->user()->role_id===2){
            $message->administrative=true;
        }
        $message->message = $request->validated("message");
        $message->globalwork_id=$globalworks->id;
        $message->save();
        return Redirect::back()->with("message","your message was successfully sent");
    }
    public function messageShow(Globalwork $globalworks):Collection
    {
        $message=$globalworks->messages();
        switch (auth()->user()->role_id){
            case 1:
                $globalworks->messages()->update(["checked_by_user"=>true]);
                break;
            case 2:
                $globalworks->messages()->update(["checked_by_admin"=>true]);
                break;
        }
        $chat=$message->orderBy("created_at")->get();
        $user=$globalworks->user()->first();
        $course=$globalworks->course()->first();
        $question=$globalworks->question()->first();
        return collect(["chat"=>$chat,"user"=>$user,"globalworks"=>$globalworks,"course"=>$course,"question"=>$question]);
    }
}
