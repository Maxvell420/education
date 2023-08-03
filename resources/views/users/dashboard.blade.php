<hearder>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</hearder>
    <body> hello {{$user["name"]}} <br>
    Courses that are accessible to join <br>
    @foreach($courses as $course)
        <a href="{{route("course.show",$course)}}">{{$course->courseName}}</a> <br>
    @endforeach
    @if($joined_courses===null)
    you have not joined any courses
    @else
        Courses that you have joined
    @foreach($joined_courses as $course)
        <a href="{{route("course.show",$course)}}">{{$course->courseName}}</a> <br>
    @endforeach
    @endif
    </body>
<form action="{{route("logout")}}">
    <button class="btn btn-warning" type="submit"> logout</button>
</form>
<a href="{{route("users.settings")}}"> settings</a>
@foreach($chats as $chat)
    <div><a href="{{route("message.show",$chat)}}"> your previous chats </a></div>
@endforeach
