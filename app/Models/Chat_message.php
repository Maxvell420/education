<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat_message extends Model
{
    use HasFactory;
    protected $fillable=["message"];
    use HasFactory;
    public function globalwork(){
        return $this->belongsTo(Globalwork::class);
    }
    public function chats(){
        return $this->globalwork();
    }
}
