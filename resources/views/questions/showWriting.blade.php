<div>
    @if (session()->has("message"))
        {{session()->get("message")}}
    @endif
</div>
<div>
    @if ($file !== null)
        <div><img src="{{asset($file->path."/".$file->given_name), }}" alt=" "></div>
    @endif
</div>
@foreach($questions as $question)
    <div>
        {{$question->problem}}
    </div>
    <div>
        total attempts for this question:{{$question->total_attempts}}
    </div>
    <div>
        your attempts for this question:{{$globalworks->num_attempts}}
    </div>
    <div>
        <form action="{{route("globalworks.update",$question)}}" method="post">
            @csrf
            @method("patch")
            <div>
                <select name="answer">
                    <option selected disabled> choose answer</option>
                    <option value="{{$questions["correct_answer"]}}"> {{$question["correct_answer"]}} </option>
                    <option value="{{$questions["incorrect_answer_1"]}}"> {{$question["incorrect_answer_1"]}}</option>
                    <option value="{{$questions["incorrect_answer_2"]}} "> {{$question["incorrect_answer_2"]}}</option>
                    <option value="{{$questions["incorrect_answer_3"]}} ">{{$question["incorrect_answer_3"]}} </option>
                </select>
            </div>
            <button type="submit"> submit </button>
        </form>
    </div>
@endforeach
{{$questions->links()}}
