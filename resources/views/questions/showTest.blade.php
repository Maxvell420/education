@if($data['examine']==0)
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
    @if ($data['file'] !== null)
        <div><img src="{{asset($data['file']->path."/".$data['file']->given_name), }}" alt=" "></div>
    @endif
</div>
<div>
{{$data['question']->problem}}
</div>
@if($data['question']==null)
<div>
total attempts for this question:{{$data['question']->total_attempts}}
</div>
<div>
your attempts for this question:{{$globalworks->items()[0]->num_attempts}}
</div>
@endif
<div>
    <form action="{{route("globalworks.update",$globalworks->items()[0])}}" method="post">
    @csrf
    @method("patch")
        <div>
            <select name="answer">
                <option selected disabled> choose answer</option>
                <option value="{{$data['question']["correct_answer"]}}"> {{$data['question']["correct_answer"]}} </option>
                <option value="{{$data['question']["incorrect_answer_1"]}}"> {{$data['question']["incorrect_answer_1"]}}</option>
                <option value="{{$data['question']["incorrect_answer_2"]}} "> {{$data['question']["incorrect_answer_2"]}}</option>
                <option value="{{$data['question']["incorrect_answer_3"]}} ">{{$data['question']["incorrect_answer_3"]}} </option>
            </select>
        </div>
            <button type="submit"> submit </button>
    </form>
</div>
@if($data['examine']!=0)
    <div>
        <a href="{{route("examine.end",[$data['course'],$data['examine']])}}"> end examine</a>
    </div>
@endif
<div>
    {{$globalworks->links()}}
</div>
