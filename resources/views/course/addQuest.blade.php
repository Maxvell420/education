<x-layout>
    <form action="{{route("questions/store",$course)}}" method="Post" enctype="multipart/form-data">
        @csrf
        <div><input type="file" name="file" placeholder="enter pdf theory for question" ></div>
        <div><input type="text" name="title" placeholder="title" value="{{old("title")}}"> </div>
        <div><input type="text" name="problem" placeholder="enter your question" value="{{old("problem")}}"> </div>
        <div><input type="text" name="answer_1" placeholder="Ответ под номером 1" value="{{old("answer_1")}}"></div>
        <div><input type="text" name="answer_2" placeholder="Ответ под номером 2" value="{{old("answer_2")}}"></div>
        <div><input type="text" name="answer_3" placeholder="Ответ под номером 3" value="{{old("answer_3")}}"></div>
        <div><input type="text" name="answer_4" placeholder="Ответ под номером 3" value="{{old("canswer_4")}}"></div>
        <div><input type="number" name ="correct_answer" max="4" min="1" value="{{old("correct_answer")}}"> Номер правильного ответа </div>
        <div><button type="submit">
                Добавить вопрос
            </button></div>
    </form>
    @foreach($errors->all() as $error)
        <li> {{$error}}</li>
    @endforeach
</x-layout>
