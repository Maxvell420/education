
<a href="{{route("questions.create",$course)}}"> add question </a> <br>
<a href="{{route("exam.create",$course)}}"> add exam for course</a>
@foreach($questions as $question)
    <a href="{{route("questions.edit",$question)}}"> {{$question->title}}</a>
@endforeach

