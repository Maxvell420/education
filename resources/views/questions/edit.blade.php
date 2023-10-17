@if($file==null)
    image wasn't downloaded
    @else <img src="{{asset($file->path."/".$file->original_name)}}" alt="image is not present">
@endif
<form method="post"  action="{{route("questions.update",$question->id)}}" enctype="multipart/form-data">
    @csrf
    @method("patch")
    <input type="file" name="file" placeholder="change image">
    <input type="text" name="title" placeholder="{{$question->title}}" value="{{old("title")}}">
    <input type="text" name="problem" placeholder="{{$question->problem}}" value="{{old("problem")}}">
    <input type="text" name="answer_1" placeholder="{{$question->answer_1}}" value="{{old("answer_1")}}">
    <input type="text" name="answer_2" placeholder="{{$question->answer_2}}" value="{{old("answer_2")}}">
    <input type="text" name="answer_3" placeholder="{{$question->answer_3}}" value="{{old("answer_3")}}">
    <input type="text" name="answer_4" placeholder="{{$question->answer_44}}" value="{{old("answer_4")}}">
    <input type="submit">
</form>
@foreach($errors->all() as $error)
    <li> {{$error}}</li>
@endforeach
