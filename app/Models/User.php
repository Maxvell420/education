<?php

namespace App\Models;

use App\Events\UserRegistrationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    protected static function booted()
    {
        static::created(function ($model){
        event(new UserRegistrationEvent($model));
        });
    }
    protected $fillable=["name","email","password"];
    protected $hidden=["password"];
    use HasFactory;
    public function roles(){
        return $this->hasOne(Role::class);
    }
    public function globalworks() {
        return $this->hasMany(Globalwork::class);
    }
    public function Note(){
        return $this->hasMany(Note::class);
    }
    public function examines(){
        return $this->hasMany(Examine::class);
    }
    public function currectExamine(){
        return $this->examines()->where("user_id",auth()->user()->id)->where("examine_closure","!=",true);
    }
}
