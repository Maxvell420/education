<hearder>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</hearder>
<a class="btn btn-primary" href="{{route("course.create")}}">course create</a>
@foreach($courses as $course)
    {{$course->courseName}}
    <a href="{{route("course.show",$course)}}"> show course </a>
    <a href="{{route("course.edit",$course)}}"> edit course </a>
@endforeach
<form action="{{route("logout")}}">
    <button class="btn btn-warning" type="submit"> logout</button>
</form>
users actions:
@foreach($notes as $note)
    <ol>
        <li>
            <div>
                {{$note->info}}
            </div>
        </li>
    </ol>
@endforeach
messages from users:
@foreach($user_messages as $user_message)
    <ol>
        <li>
            <div>
                has sent new message: <a href="{{route("message.show",$user_message->globalwork)}}"> {{$user_message->globalwork->user->name}} </a>
            </div>
        </li>
    </ol>
@endforeach
<div> <a href="{{route("chats")}}"> chats with users</a> </div>
