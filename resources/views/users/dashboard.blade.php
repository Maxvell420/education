<x-layout>
    Курсы в которые вы можете вступить<br>
    @foreach($courses as $course)
        <a href="{{route("course.show",$course)}}">{{$course->courseName}}</a> <br>
    @endforeach
    @if($joined_courses===null)
        У вас нет курсов в которые вы вступили
    @else
        Курсы в которые вы вступили
        @foreach($joined_courses as $course)
            <a href="{{route("course.show",$course)}}">{{$course->courseName}}</a> <br>
        @endforeach
    @endif
</x-layout>

