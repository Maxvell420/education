@if (session()->has("message"))
    {{session()->get("message")}}
@endif
@foreach($exams as $exam)
    <a href="{{route("exam.warning",$exam)}}"> start exam</a>
    @if (session()->has("warning"))
        {{session()->get("warning")}}
        <a href="{{route("examine.start",[$exam])}}">start exam</a>
    @endif
    <a href="{{route("examine.results",[$exam])}}">results of this exam</a>
@endforeach
@if($course->course_complete==null)
    <div>
        <form action="{{route("course/open",$course)}}" method="post">
            @csrf
            @method("patch")
            <button type="submit"> open {{$course->courseName}} course for joining </button>
        </form>
    </div>
@endif
<a href="{{route("question/show",[$course])}}"> course questions</a>
@if($questions==0)
<form action="{{route("course/join",$course)}}" method="POST">
    @csrf
<button type="submit">
    join course
</button> </form>
@endif
@if (session()->has("refresh"))
    {{session()->get("refresh")}}
<form action="{{route("course/refresh",[$course])}}" method="post">
    @csrf
    <button type="submit" >refresh course </button>
</form>
@endif
