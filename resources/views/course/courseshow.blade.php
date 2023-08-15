@if (session()->has("message"))
    {{session()->get("message")}}
@endif
@foreach($data['exams'] as $exam)
    <a href="{{route("examine.start",[$data['course'],$exam])}}">start exam</a>
    <a href="{{route("examine.results",$exam)}}">results of this exam</a>
@endforeach
@if($data['course']->course_complete==null)
    <div>
        <form action="{{route("course.open",$data['course'])}}" method="post">
            @csrf
            @method("patch")
            <button type="submit"> open {{$data['course']->courseName}} course for joining </button>
        </form>
    </div>
@endif
<a href="{{route("globalworks.show",$data['course'])}}"> course questions</a>
@if($data['questions']==0)
<form action="{{route("globalworks.create",$data['course'])}}" method="POST">
    @csrf
<button type="submit">
    join course
</button> </form>
@endif
@if (session()->has("refresh"))
    {{session()->get("refresh")}}
<form action="{{route("globalworks.refresh",$data['course'])}}" method="post">
    @csrf
    <button type="submit" >refresh course </button>
</form>
@endif
