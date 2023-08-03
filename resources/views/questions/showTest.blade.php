@if($examine==0)
<div>
    @if (session()->has("message"))
        {{session()->get("message")}}
    @endif
</div>
@endif
<div>
    @if (session()->has("warning"))
        {{session()->get("warning")}}
    @endif
</div>
<div>
    @if ($file !== null)
        <div><img src="{{asset($file->path."/".$file->given_name), }}" alt=" "></div>
    @endif
</div>
<div>
{{$question->problem}}
</div>
@if($examine==0)
<div>
total attempts for this question:{{$question->total_attempts}}
</div>
<div>
your attempts for this question:{{$globalworks->items()[0]->num_attempts}}
</div>
@endif
<div>
    <form action="{{route("globalworks.update",[$question,$examine])}}" method="post">
    @csrf
    @method("patch")
        <div>
            <select name="answer">
                <option selected disabled> choose answer</option>
                <option value="{{$question["correct_answer"]}}"> {{$question["correct_answer"]}} </option>
                <option value="{{$question["incorrect_answer_1"]}}"> {{$question["incorrect_answer_1"]}}</option>
                <option value="{{$question["incorrect_answer_2"]}} "> {{$question["incorrect_answer_2"]}}</option>
                <option value="{{$question["incorrect_answer_3"]}} ">{{$question["incorrect_answer_3"]}} </option>
            </select>
        </div>
            <button type="submit"> submit </button>
    </form>
</div>
@if($examine!=0)
    <div>
        <a href="{{route("examine.end",[$course,$examine])}}"> end examine</a>
    </div>
@endif
<div>
    {{$globalworks->links()}}
</div>
