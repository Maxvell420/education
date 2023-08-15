<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{UsersController,QuestionController,BotController,CourseController,
    ExamController, ExamineController,MessageController,GlobalworkController};


Route::get("notification", function () {return view("notifications");});

Route::get("users/registration",[UsersController::class,"create"])->name("users/create");


Route::get("",[UsersController::class,"login"])->name("login");


Route::prefix("course")->group(function (){
    Route::get("/create",[CourseController::class,"create"])->name("course.create")->middleware("admincheck");
    Route::post("/store",[CourseController::class,"store"])->name("Course.store")->middleware("admincheck");
    Route::get("/{course}/edit",[CourseController::class,"edit"])->name("course.edit")->middleware("admincheck");
//    Route::get("/{course}/edit/adduser",[QuestionController::class,"addUser"])->name("course.adduser")->middleware("admincheck");
    Route::get("/{course}/show",[CourseController::class,"show"])->name("course.show");
    Route::patch("/{course}/open",[CourseController::class,"open"])->name("course.open");
})->middleware("auth");

Route::prefix('examine')->group(function(){
    Route::get("/{course}/exam_create",[ExamController::class,"create"])->name("exam.create");
    Route::post("/{course}/exam_store",[ExamController::class,"store"])->name("exam.store");
})->middleware("auth");

Route::prefix('examine')->group(function(){
    Route::get("/{course}/{exam}/examineCreate",[ExamineController::class,"create"])->name("examine.start");
    Route::get("/{exam}/results",[ExamineController::class,"results"])->name("examine.results");
    Route::get("/{course}/{examine}/end",[ExamineController::class,"end"])->name("examine.end");
    })->middleware("auth");

Route::get("dashboard",[QuestionController::class,"dashboard"])->name("dashboard")->middleware("auth");
Route::post("auth",[UsersController::class,"authenticate"])->name("auth");
Route::get("logout",[QuestionController::class,"logout"])->name("logout");
Route::get("admindashboard",[UsersController::class,"admindashboard"])->name("admindashboard")->middleware("auth","admincheck");

Route::prefix("globalworks")->group(function () {
    Route::get("/show/{course}",[GlobalworkController::class,"show"])->name("globalworks.show")->middleware(["ExamineExpire"]);
    Route::patch("/{question}/update/{examine?}",[GlobalworkController::class,"Update"])->name("globalworks.update");
    Route::post("/{course}/refresh",[QuestionController::class,"Refresh"])->name("globalworks.refresh");
    Route::post("/{course}/join/{exam?}/{examine?}",[QuestionController::class,"Create"])->name("globalworks.create");
})->middleware("auth");

Route::prefix("message")->group(function (){
    Route::get("/{globalworks}/show",[MessageController::class,'Show'])->name("message.show");
    Route::get("/{globalworks}/error",[MessageController::class,'Error'])->name("message.error");
    Route::post("/{globalworks}/store",[MessageController::class,'Store'])->name("message.store");
    Route::get("/chats",[UsersController::class,"chats"])->name('chats');
});

Route::prefix("questions")->group(function (){
    Route::post("questions/store/{course}",[QuestionController::class,"store"])->name('questions/store');
    Route::get("questions/create/{course}",[QuestionController::class,"create"])->name("questions.create");
})->middleware(["auth","admincheck"]);
Route::resource("questions",QuestionController::class)->except("create","show","store");
Route::prefix("users")->group(function (){
    Route::post("/store",[UsersController::class,"store"])->name("users/store");
    Route::get("/settings",[UsersController::class,"settings"])->name("users.settings")->middleware(["auth"]);
});
Route::resource("users",UsersController::class)->except("create","store")->middleware(["IDcheck"]);
