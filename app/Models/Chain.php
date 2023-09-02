<?php

namespace App\Models;

use App\Events\ChainCreateEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    use HasFactory;
    protected $fillable=['user_id','globalwork_id','course_id','question_id','admin'];

    public function Globalwork()
    {
        $this->belongsTo(Globalwork::class);
    }
}
