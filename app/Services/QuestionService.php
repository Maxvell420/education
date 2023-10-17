<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Downloads;
use App\Models\Question;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class QuestionService
{
    private function requestValidate(Request $request,Question $question=null)
    {
        $rules = [
            "title"=>["min:3","max:30"],
            "problem"=>["min:3","max:255"],
            'answer_1'=>["min:3","max:100"],
            'answer_2'=>["min:3","max:100"],
            'answer_3'=>["min:3","max:100"],
            'answer_4'=>["min:3","max:100"],
            'correct_answer'=>['ends_with:1,2,3,4','size:1'],
        ];
        $update = [];
        if ($question) {
            foreach ($rules as $rule => $value) {
                if ($request->$rule){
                    $update[$rule]=$value;
                }
            }
        } else {
            foreach ($rules as $rule => $value) {
                $value[]='required';
                $update[$rule]=$value;
            }
        }
        return $request->validate($update);
    }
    public function questionCreate(Request $request,Course $course): \Illuminate\Database\Eloquent\Model
    {
        $data = $this->requestValidate($request);
        $question=$course->questions()->create($data);
        if ($request->file){
            $this->fileStore($request->file,$question);
        }
        return $question;
    }
    private function telegram_store(Downloads $download):string
    {
        $url = Url::first();
        $update = Telegram::sendPhoto(['chat_id'=>1955425357,'photo'=>\Telegram\Bot\FileUpload\InputFile::create($url->url.'/'.$download->path.'/'.$download->original_name)]);
        foreach ($update->photo as $file) {
            $file_id = $file->file_id;
            break;
        }
        return $file_id;
    }
    private function download_file_id(Downloads $download,string $file_id):void
    {
        $download->update(['file_id'=>$file_id]);
    }
    public function fileStore(UploadedFile $file,Question $question):void
    {
        $filenameOriginal='Название:'.$question->title.'.jpg';
        $course=$question->course()->first();
        $string=$course->courseName."/".$question->title;
        $path = str_replace(' ', '', $string);
        $file->storeAs("questionStorage");
        $file->move(public_path($path),$filenameOriginal);
        $download = $question->downloads()->create(["course_id"=>$course->id,
            "path"=>$path,
            "original_name"=>$filenameOriginal]);
        $file_id = $this->telegram_store($download);
        $this->download_file_id($download,$file_id);
    }
    public function questionUpdate(Request $request,Question $question):Question
    {
        $data =$this->requestValidate($request,$question);
        $question->update($data);
        return $question;
    }
    public function fileUpdate(Request $request, Question $question):void
    {
        $download = $question->downloads()->first();
        if ($download) {
            $this->fileDelete($download);
        }
         $this->fileStore($request->file,$question);
    }
    public function fileDelete(Downloads $download):void
    {
        unlink($download->path.'/'.$download->original_name);
        rmdir($download->path);
        $download->delete();
    }
    public function questionDelete(Question $question):void
    {
        $download = $question->downloads()->first();
        if ($download) {
            $this->fileDelete($download);
        }
        $question->delete();

    }
}
