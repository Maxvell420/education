<?php

namespace App\Services;

use App\Http\Requests\QuestionRequest;
use App\Models\Course;
use App\Models\Downloads;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionService
{
    public function questionCreate(QuestionRequest $request,Course $course){
        $question=$course->questions()->create($request->validated());
        /*
         * добавляет вопрос в таблицу questions, и ответы в таблицу answers с id равным максимальному id из таблицы questions
         * */
        if ($request->file("file")!==null) {
            $this->filestore($request,$question);
        }
    }
    public function fileStore(Request $request,Question $question) {
        $file=$request->file("file");
        $filenameOriginal=$request->file("file")->getClientOriginalName();
        $formatPointNum=strripos($filenameOriginal,".");
        $formatLenghtNum=strlen($filenameOriginal);
        $formatNum=$formatLenghtNum-$formatPointNum;
        $format=mb_substr($filenameOriginal,-$formatNum,$formatNum);
        $course=$question->course()->first();
        $filename=$course->courseName."-".$question->title.$format;
        $path=$course->courseName."/".$question->title;
        $file->storeAs("questionStorage",$filename);
        $file->move(public_path($path),$filename);
        Downloads::query()->Create(["question_id"=>$question->id,
            "course_id"=>$course->id,
            "path"=>$path,
            "original_name"=>$filenameOriginal,
            "given_name"=>$filename]);

    }
    public function Handle(){

    }
}
