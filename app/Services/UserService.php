<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Examine;
use App\Models\Globalwork;
use App\Models\User;
use Illuminate\Support\Collection;

class UserService
{
    public User $user;
    public function __construct(User $user)
    {
        $this->user=$user;
    }
    public function joinedCourses():Collection
    {
        return Course::whereHas('globalworks',function ($query){
            $query->where('user_id',$this->user->id);
        })->get();
    }
    public function availableCourses():Collection
    {
        return Course::all()->diff($this->joinedCourses())->where("course_complete",true);
    }
    public function get_user_id():int
    {
        return $this->user->id;
    }
    public function courseJoin(Course $course, Examine $examine=null, Collection $questions = null):Collection
    {
        if ($questions == null) {
            $questions = $course->questions()->orderBy('id')->get();
        }
        $globalworks=Globalwork::where(['user_id' => $this->user->id,
            "course_id" => $course->id,
            "examine_id" => $examine?->id])->get();
        if ($globalworks->isNotEmpty()) {
            return collect(['text'=>'You already have joined '. $course->courseName,'globalwork'=>$globalworks->first()->id]);
        }
        $array=[];
        foreach ($questions as $question) {
                $array[]= [
                    'question_id'=>$question->id,
                    'user_id' => $this->user->id,
                    "course_id" => $course->id,
                    "examine_id" => $examine?->id
                ];
        }
        $globalworks=Globalwork::insert($array);
        return collect(['text'=>'You have successful joined course'. ' ' . $course->courseName,'globalwork'=>$globalworks->first()]);
    }
    public function GlobalworksCheck(Course $course)
    {
        $globalworks=$course->getGlobalwokrs($this->user->id)->get();

    }
}
