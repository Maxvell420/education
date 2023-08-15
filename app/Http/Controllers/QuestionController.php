<?php

namespace App\Http\Controllers;

use App\Events\ExamineStartEvent;
use App\Models\Examine;
use App\Http\Requests\AnswersRequest;
use App\Http\Requests\ExamRequest;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\QuestionRequest;
use App\Http\Requests\QuestionUpdateRequest;
use App\Models\Chat_message;
use App\Models\Course;
use App\Models\Downloads;
use App\Models\Exam;
use App\Models\Globalwork;
use App\Models\Question;
use App\Models\User;
use App\Services\GlobalworkService;
use App\Services\QuestionService;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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
    public function store(QuestionRequest $request,Course $course):RedirectResponse
    {
        $question=new QuestionService();
        $question->questionCreate($request,$course);
        return redirect("admindashboard");
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        $Globalwork= new GlobalworkService();
        $data=$Globalwork->globalworkShow($course);
        if ($data['question']->question_type=="test") {
            return view("questions.showTest",['data'=>$data]);
        }
        else {
            return view("questions.showWriting",['data'=>$data]);
        }
    }

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
        return redirect("admindashboard");
    }

    public function globalworksUpdate(AnswersRequest $request,Question $question,int $examine=null):RedirectResponse
    {
        if ($request->answer == $question->correct_answer){
            $question->userAnswer($examine)->increment("num_attempts",1,["user_answer"=>"correct","answer_check"=>true]);
            $totalAttempts=$question->globalworks()->sum("num_attempts");
            Question::find($question->id)->update(["total_attempts"=>$totalAttempts]);
            //если exam то без сообщения сделано во вьюхе
            return Redirect::back()->with("message","Good job! you may proceed");
        }
        else {
            $question->userAnswer($examine)->increment("num_attempts",1,["user_answer"=>$request->answer]);
            $totalAttempts=$question->globalworks()->sum("num_attempts");
            Question::find($question->id)->update(["total_attempts"=>$totalAttempts]);
            return Redirect::back()->with("message","Answer is not correct");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question):RedirectResponse
    {
        $question->delete();
        return redirect("admindashboard");
    }

    public function logout(Request $request):RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("logout");
    }

    public function courseCreate():Renderable
    {
        return view("course.coursecreate");
    }

    public function courseStore(Request $request):RedirectResponse
    {
        Course::query()->create(["courseName" => $request->input("courseName")]);
        return redirect("admindashboard");
    }

    public function courseEdit(Course $course):Renderable
    {
        $questions=Question::where("course_id",$course->id)->get();
        return view("course.courseedit", ["course" => $course,"questions"=>$questions]);
    }

    public function courseShow(Course $course):Renderable
    {
        $exams=$course->exams()->get();
        $courseQuestions=$course->questions();
        $usersQuestions=$course->globalworksGet()->get()->count();
        if ($usersQuestions!=$courseQuestions->count() and $usersQuestions>0) {
            session()->flash("refresh","since you joined course"." ".$course->courseName." "."was updated, there is button bellow to update it for you");
        }
        return view("course.courseshow", ["course"=>$course,"exams"=>$exams,"questions"=>$usersQuestions]);
    }
    public function courseRefresh(Course $course){
    $course->courseRefresh();
    $this->courseJoin($course);
        session()->flash("message","You have successful refreshed course"." ".$course->courseName." "."there are questions of this course bellow");
        return redirect()->route("course.show",$course);
    }
    public function courseJoin(Course $course):RedirectResponse
    {
        //сделать 1 запросом к бд
        $table=$course->questions()->get();
        foreach ($table as $entities)
        {
            Globalwork::query()->create(["question_id"=>$entities->id,
                "course_id"=>$entities->course_id,
                "user_id"=>auth()->id(),]);
        }
        session()->flash("message","You have successful joined course"." ".$course->courseName." "."there are questions of this course bellow");
        return redirect()->route("course.show",$course);
    }
    public function dashboard():Renderable
    {
        $user=Auth::user();
        $joined_courses= Course::whereHas("globalworksGet")->get();
        $available_courses=Course::all()->diff($joined_courses)->where("course_complete","!=",null);
        $chats=Globalwork::has("messages")->where("user_id",\auth()->user()->id)->get();
        return view("users.dashboard", ["user" => $user, "courses" => $available_courses, "joined_courses" => $joined_courses,"chats"=>$chats]);
    }
    public function courseOpen(Course $course){
        $course->update(["course_complete"=>true]);
        return redirect()->route("course.show",$course);
    }
    public function message(Question $question,Globalwork $globalworks){
        return view("questions.messagesForm",["question"=>$question,"globalworks"=>$globalworks]);
    }
    public function messageStore(MessageRequest $request,Globalwork $globalworks){
        $message = new Chat_message();
        if (auth()->user()->role_id===2){
            $message->administrative=true;
        }
        $message->message = $request->validated("message");
        $message->globalwork_id=$globalworks->id;
        $message->save();
        return Redirect::back()->with("message","your message was successfully sent");
    }
    public function messageShow(Globalwork $globalworks){
        $message=$globalworks->messages();
        switch (auth()->user()->role_id){
            case 1:
                $globalworks->messages()->update(["checked_by_user"=>true]);
                break;
            case 2:
                $globalworks->messages()->update(["checked_by_admin"=>true]);
                break;
        }
        $chat=$message->orderBy("created_at")->get();
        $user=$globalworks->user()->first();
        $course=$globalworks->course()->first();
        $question=$globalworks->question()->first();
        return view("messages.show",["chat"=>$chat,"user"=>$user,"globalworks"=>$globalworks,"course"=>$course,"question"=>$question]);
    }
    public function examCreate(Course $course){
        $MaxNumber=$course->questions()->count();
        return view("course.examCreate",["course"=>$course,"MaxNumber"=>$MaxNumber]);
    }
    public function examStore(ExamRequest $request,Course $course){
        $course->exams()->create($request->validated());
        return redirect("admindashboard");
    }
    public function examWarning(Exam $exam){
    return Redirect::back()->with("warning","when you start this exam you will have only".$exam->minutes_for_exam." minutes to finish with no pause");
    }
    public function examineCreate(Exam $exam)
    {
        $examine=$exam->examines()->create(["user_id"=>\auth()->user()->id,
            "examine_expires"=>$seconds=now()->addMinutes($exam->minutes_for_exam)]);
        return redirect()->route("question/show",[$this->examWorksCreate($exam,$examine,$seconds)]);

    }
    public function examWorksCreate(Exam $exam,Examine $examine,Carbon $seconds){
        $query = [];
        $course=$exam->course()->first();
        $questions = $course->questions()->get()->modelKeys();
        $exams_questionsID = array_rand(array_flip($questions), $exam->questions_num);
        foreach ($exams_questionsID as $exam_question) {
            $query[] = ["question_id" => $exam_question,
                "course_id"=>$course->id,
                "user_id" => $examine->user_id,
                "examine_id" => $examine->id,
                "created_at" => now(),
                "updated_at" => now()];
        }
        Globalwork::insert($query);
        event(new ExamineStartEvent($examine->id,$seconds->diffInSeconds()));
        return $course;
    }
    public function examineResults(Exam $exam){
        $examinesInfo=[];
        $examines=$exam->examines()->get();
        foreach ($examines as $examine){
            $globalworks[]=$examine->globalworks()->get();
            $examinesInfo[]=collect($examine)->concat($globalworks);
        }
        return view("course.ExamineResults",["examines"=>$examinesInfo]);
    }
    public function examineEnd(Course $course,int $examine){
    event(new ExamineStartEvent($examine));
    return \redirect()->route("course.show",[$course]);
    }
}
