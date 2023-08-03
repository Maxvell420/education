<form action="{{route("questions/store",$course)}}" method="Post" enctype="multipart/form-data">
    @csrf
    <div><input type="file" name="file" placeholder="enter pdf theory for question" ></div>
    <div><input type="text" name="title" placeholder="title" value="{{old("title")}}"> </div>
    <div><input type="text" name="problem" placeholder="enter your question" value="{{old("problem")}}"> </div>
    choose type of answers that user must provide
    <div><select name="question_type" >
        <option value="test"> test</option>
        <option value="writing">writing</option>
    </select></div>
    <div><input type="text" name="correct_answer" placeholder="enter correct answer to your question" value="{{old("correct_answer")}}"></div>
     <div><input type="text" name="incorrect_answer_1" placeholder="enter false variant" value="{{old("incorrect_answer_1")}}"></div>
     <div><input type="text" name="incorrect_answer_2" placeholder="enter false variant" value="{{old("incorrect_answer_2")}}"></div>
     <div><input type="text" name="incorrect_answer_3" placeholder="enter false variant" value="{{old("incorrect_answer_3")}}"></div>
     <div><button type="submit">
        add Question
    </button></div>
</form>
@foreach($errors->all() as $error)
    <li> {{$error}}</li>
@endforeach
