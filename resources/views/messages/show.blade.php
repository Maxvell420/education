@if(auth()->user()->role_id===2)
    <div>your chat with {{$user->name}}</div>
        @else<div>chat with admin</div>
@endif
about {{$question->problem}} in {{$course->courseName}}
<div>
@foreach($chat as $message)
    @if($message->administrative===true)
            <div>{{"ADMIN:".$message->message." ".$message->created_at}}</div>
        @else <div>{{$user->name.":".$message->message." ".$message->created_at}}</div>
        @endif
    @endforeach
</div>
<div>
    <form action="{{route("globalworks.messageStore",$globalworks)}}" method="post">
        @csrf
        <div>
            <input type="text" name="message" >
        </div>
        <div>
            <button type="submit"> post </button>
        </div>
    </form>
</div>
