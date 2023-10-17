<?php

namespace App\Models;

use App\Events\UserRegistrationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
class User extends Authenticatable
{

    protected $fillable=["name","email","password",'telegram_id','token'];
    protected $hidden=["password"];
    use HasFactory,HasApiTokens;
    public function chain(){
        return $this->hasOne(Chain::class);
    }
    public function role(){
        return $this->hasOne(Role::class);
    }
    public function globalworks() {
        return $this->hasMany(Globalwork::class);
    }
    public function examines()
    {
        return $this->hasMany(Examine::class);
    }
    public function currectExamine()
    {
        return $this->examines()->where('examine_closure',false);
    }
}
