<?php

namespace App\Http\Controllers;

use App\Models\{Course,Question};
use App\Services\QuestionService;
use Illuminate\{Contracts\Support\Renderable, Http\RedirectResponse, Http\Request};

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
    public function store(Request $request,Course $course):RedirectResponse
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

    public function update(Request $request,Question $question)
    {
        $update= new QuestionService();
        $question=$update->questionUpdate($request,$question);
        if ($request->file('file')) {
            $update->fileUpdate($request,$question);
        }
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
