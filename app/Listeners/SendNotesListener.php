<?php

namespace App\Listeners;

class SendNotesListener
{
    protected $fillable=["info"];
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $user=$event->model;
        $user->note()->create(["info"=>$user->name." "."has registered"." ".$user->created_at]);
    }
}
