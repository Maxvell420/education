<?php

namespace App\Jobs;

use App\Models\Examine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExamineClosureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $id;
    /**
     * Create a new job instance.
     */
    public function __construct($id)
    {
        $this->id=$id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $array=[];
        $examine=Examine::find($this->id);
        $answers=$examine->globalworks()->get("answer_check");
        foreach ($answers as $answer){
            if($answer->answer_check){
                $array[]=1;
            }
        }
        $examine->update(["correct_answers_percentage"=>(array_sum($array)*100/$answers->count()),"examine_closure"=>true,["exam_finished_in"=>now()]]);
    }
}
