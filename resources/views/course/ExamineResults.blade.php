This is your results in :
@foreach($examines as $examine)
    <div>{{$examine->get("user_answer")}}</div>
    <div>{{$examine->get("id")}} сделать попытки?</div>
    <div>{{$examine->get("correct_answers_percentage")}}%</div>
    @foreach($examine[0] as $globalworks)
             <div>
                 @if($globalworks->user_answer==null)
                        <div>Answer: no answer</div>
                 @else
                        <div>{{$globalworks->user_answer}}</div>
                 @endif
             </div>
    @endforeach
@endforeach
