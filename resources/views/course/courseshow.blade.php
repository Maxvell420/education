@if (session()->has("message"))
    {{session()->get("message")}}
@endif
@foreach($data['exams'] as $exam)
    <a href="{{route("examine.start",[$exam])}}">start exam</a>
    <a href="{{route("examine.results",[$exam])}}">results of this exam</a>
@endforeach
@if($course->course_complete==null)
    <div>
        <form action="{{route("course/open",$data['course'])}}" method="post">
            @csrf
            @method("patch")
            <button type="submit"> open {{$data['course']->courseName}} course for joining </button>
        </form>
    </div>
@endif
<a href="{{route("question/show",$data['course'])}}"> course questions</a>
@if($questions==0)
<form action="{{route("course/join",$data['course'])}}" method="POST">
    @csrf
<button type="submit">
    join course
</button> </form>
@endif
@if (session()->has("refresh"))
    {{session()->get("refresh")}}
<form action="{{route("course/refresh",$data['course'])}}" method="post">
    @csrf
    <button type="submit" >refresh course </button>
</form>
@endif
