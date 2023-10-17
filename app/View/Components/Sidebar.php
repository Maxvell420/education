<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Sidebar extends Component
{

    public function render()
    {
        $user = Auth::user();
        return view('components.sidebar',['user'=>$user]);
    }
}
