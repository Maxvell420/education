@if($file===null)
    image wasn't downloaded
    @else <img src="{{asset($file->path."/".$file->given_name)}}" alt="image is not present">
@endif
<form method="post"  action="{{route("questions.update",$question->id)}}" enctype="multipart/form-data">
    @csrf
    @method("patch")
    <input type="file" name="file" placeholder="change image">
    <input type="text" name="title" placeholder="{{$question->title}}" value="{{old("title")}}">
    <input type="text" name="problem" placeholder="{{$question->problem}}" value="{{old("problem")}}">
    <input type="text" name="correctAnswer" placeholder="{{$question->correct_answer}}" value="{{old("correctAnswer")}}">
    <input type="text" name="incorrect_answer_1" placeholder="{{$question->incorrect_answer_1}}" value="{{old("incorrect_answer_1")}}">
    <input type="text" name="incorrect_answer_2" placeholder="{{$question->incorrect_answer_2}}" value="{{old("incorrect_answer_2")}}">
    <input type="text" name="incorrect_answer_3" placeholder="{{$question->incorrect_answer_3}}" value="{{old("incorrect_answer_3")}}">
    <input type="submit">
</form>
@foreach($errors->all() as $error)
    <li> {{$error}}</li>
@endforeach
