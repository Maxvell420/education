<?php

namespace App\Services;

use App\Http\Requests\QuestionUpdateRequest;
use App\Models\Course;
use App\Models\Downloads;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionService
{
    private function requestValidate(Request $request)
    {
        return $request->validate([
            "title"=>["required","min:3","max:30"],
            "problem"=>["required","min:3","max:255"],
            'answer_1'=>["required","min:3","max:100"],
            'answer_2'=>["required","min:3","max:100"],
            'answer_3'=>["required","min:3","max:100"],
            'answer_4'=>["required","min:3","max:100"],
            'correct_answer'=>['required','ends_with:1,2,3,4','size:1'],
        ]);
    }
    public function questionCreate(Request $request,Course $course): \Illuminate\Database\Eloquent\Model
    {
        $question=$course->questions()->find($request->question_id);
        $question=$course->questions()->updateOrCreate(['id'=>$question->id??-1],$this->requestValidate($request));
//        $question->update($this->requestValidate($request));
        if ($request->file_id!=null) {
            $question->downloads()->updateOrCreate(['question_id'=>$question->id],['file_id'=>$request->file_id,'course_id'=>$course->id]);
        }
        return $question;
    }
    public function fileStore(Request $request,Question $question):void
    {
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
    public function questionUpdate(QuestionUpdateRequest $request,Question $question):string
    {
        if ($question->downloads()->first() !== null) {
            $file=$question->downloads()->first();
            $file->delete();
            if (file_exists(public_path($file->path.'/'.$file->given_name)))
            {
                unlink(public_path($file->path.'/'.$file->given_name));
            }
        }
        if ($request->file('file'))
        {
            $this->fileStore($request,$question);
        }
        $question->update($request->validated());
        /* Удаляет картинку к вопросу из папки public*/
        return 'question was updated with success';
    }
}
