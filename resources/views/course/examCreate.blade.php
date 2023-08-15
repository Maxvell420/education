<div>
    <form method="post" action="{{route("exam.store",$data['course'])}}">
        @csrf
        <input type="range" min="0" max="{{$data['MaxNumber']}}"  name="questions_num">
        <input type="number" name="minutes_for_exam">
        <button type="submit"> create exam</button>
    </form>
</div>
