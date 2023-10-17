<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Services\UserService;
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
        return redirect()->route("login");
    }
    public function login(User $user)
    {
        return view("users.login", ["user" => $user]);
    }
    public function admindashboard()
    {
        $course=Course::all();
        return view("questions.admindashboard",["courses"=>$course]);
    }
    public function settings()
    {   $user=auth()->user();
        return view("users.settings",["user"=>$user]);
    }

    public function dashboard():Renderable
    {
        $user=Auth::user();
        $service = new UserService($user);
        $joined_courses=$service->joinedCourses();
        $available_courses=$service->availableCourses();
        return view("users.dashboard", ["user" => $user, "courses" => $available_courses, "joined_courses" => $joined_courses]);
    }
    public function projInfo():Renderable
    {
        return view('welcome');
    }
}
