<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\{Chat_message,Course,Globalwork,Note,User};
use Illuminate\{Contracts\Support\Renderable,Http\RedirectResponse,Http\Request,
    Support\Facades\Hash, Validation\ValidationException,Support\Facades\Auth};

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Users=User::orderBy('id')->paginate(3);
        return view("users.list",["users"=>$Users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
            return view("users.registration");
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(UserRequest $request)
    {
        User::create(
                ["name"=>$request->name,
                "email"=>$request->email,
                "password"=>Hash::make($request->password)]
        );
        return redirect()->route("login");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view("users.show",["user"=>$user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {

        return view("users.edit",["user"=>$user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $user->update(["name"=>$request->name,
            "email"=>$request->email,
            "password"=>Hash::make($request->password)]);
        return redirect()->route("dashboard");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route("users.index");
    }
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ["required", "email"],
            'password' => ['required']]);
        if (Auth::attempt($credentials)) {
            /*после успешной попытки создается авторизация и идет проверка пользователя на роль Админ/юзер
            по столбцу БД*/
            $request->session()->regenerate();
            if (auth()->user()->role_id >1) {
                return redirect()->intended('admindashboard');
            }
            return redirect()->intended('dashboard');
            /*Добавить страницу для простых пользователей*/
        }
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    public function logout(Request $request):RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route("logout");
    }
    public function login(User $user)
    {
        return view("users.login", ["user" => $user]);
    }
    public function admindashboard()
    {
        $course=Course::all();
        $notes=Note::all();
        $user_message=Chat_message::where("checked_by_admin","!=",true)->latest()->get();
        return view("questions.admindashboard",["courses"=>$course,"notes"=>$notes,"user_messages"=>$user_message]);
    }
    public function settings()
    {   $user=auth()->user();
        return view("users.settings",["user"=>$user]);
    }
    public function chats(){
        $info=[];
        $chats=Globalwork::has("messages")->distinct()->get();
        foreach ($chats as $chat){
            $info[]=["userName"=>$chat->user()->first()->name,"globalworks"=>$chat];
        }
        return view("messages.chats",["info"=>$info]);
    }
    public function dashboard():Renderable
    {
        $user=Auth::user();
        $joined_courses= Course::whereHas("globalworksGet")->get();
        $available_courses=Course::all()->diff($joined_courses)->where("course_complete","!=",null);
        $chats=Globalwork::has("messages")->where("user_id",\auth()->user()->id)->get();
        return view("users.dashboard", ["user" => $user, "courses" => $available_courses, "joined_courses" => $joined_courses,"chats"=>$chats]);
    }
}
