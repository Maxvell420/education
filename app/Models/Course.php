<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Course extends Model
{
    use HasFactory;
    protected $fillable=["courseName","course_complete",'course_info'];
    public function questions(){
        return $this->hasMany(Question::class);
    }
    public function globalworks(){
        return $this->hasMany(Globalwork::class);
    }
    public function courseRefresh()
    {
        return $this->globalworksGet()->delete();
    }
    public function globalworksGet($id=null)
    {
        $query=$this->globalworks()->where("user_id","=",auth()->user()->id)->where("examine_id",$id);
        return $query;
    }
    public function downloads(){
        return $this->hasMany(Downloads::class);
    }
    public function exams(){
        return $this->hasMany(Exam::class);
    }
    public function getUsersGlobalworks(int $user_id, ?int $examine_id=null)
    {
        return $this->globalworks()->where("user_id",$user_id)->where("examine_id",$examine_id);
    }
}
