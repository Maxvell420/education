Bellow you can write mistakes in {{$question->problem}} question
<div>
    <form action="{{route("message.store",$globalworks)}}" method="post">
        @csrf
        <div>
            <input type="text" name="message" >
        </div>
        <div>
            <button type="submit"> post </button>
        </div>
    </form>
</div>
@if (session()->has("message"))
    {{session()->get("message")}}
@endif
