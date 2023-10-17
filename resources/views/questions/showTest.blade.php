<x-layout>
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
        <div><img src="{{asset($data['file']->path.'/'.$data['file']->original_name), }}" alt=" "></div>
    @endif
</div>
<div>
{{$data['Название']->problem}}
</div>
@if($data['Название']==null)
<div>
total attempts for this question:{{$data['Название']->total_attempts}}
</div>
<div>
your attempts for this question:{{$globalworks->items()[0]->num_attempts}}
</div>
@endif
<div>
    <form action="{{route("globalworks.update",[$globalworks->items()[0]])}}" method="post">
    @csrf
    @method("patch")
        <div>
            <select name="answer">
                <option selected disabled> choose answer</option>
                <option> {{$data['Название']["answer_1"]}} </option>
                <option> {{$data['Название']["answer_2"]}}</option>
                <option> {{$data['Название']["answer_3"]}}</option>
                <option> {{$data['Название']["answer_4"]}} </option>
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
</x-layout>
