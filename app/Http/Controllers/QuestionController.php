<?php

namespace App\Http\Controllers;

use App\Http\Requests\{QuestionRequest,QuestionUpdateRequest};
use App\Models\{Course,Question};
use App\Services\QuestionService;
use Illuminate\{Contracts\Support\Renderable,Http\RedirectResponse};

class QuestionController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course):Renderable
    {
        return view("course.addQuest",["course"=>$course]);
    }

    /**
     * Store a newly created Question and checks request on file existence
     */
    public function store($request,Course $course):RedirectResponse
    {
        $question=new QuestionService();
        $question->questionCreate($request,$course);
        return redirect("admindashboard");
    }

    /**
     * Display the specified resource.
     */

    public function edit(Question $question)
    {
        $file=$question->downloads()->first();
        return view("questions.edit",["question"=>$question,"file"=>$file]);
    }
    /*
     метод update:
     Проверяет наличие файла в папке public, затем если старый файл существует то удаляет его; При наличии файла в запросе загружает его,
    удаляя старую запись о файле и сохраняя новый файл через метод filestore (см.выше) и затем обновляет в бд ответы на вопрос.
     */

    public function update(QuestionUpdateRequest $request,Question $question)
    {
        $update= new QuestionService();
        $update->questionUpdate($request,$question);
        return redirect("admindashboard");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question):RedirectResponse
    {
        $question->delete();
        return redirect("admindashboard");
    }
}
