<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\QuestionController;

Route::get("notification", function () {return view("notifications");});

Route::prefix("users")->group(function (){
    Route::post("/store",[UsersController::class,"store"])->name("users/store");
    Route::get("/settings",[UsersController::class,"settings"])->name("users.settings")->middleware(["auth"]);
});
Route::get("users/registration",[UsersController::class,"create"])->name("users/create");


Route::get("",[UsersController::class,"login"])->name("login");

Route::prefix("questions")->group(function (){
    Route::post("questions/store/{course}",[QuestionController::class,"store"])->name('questions/store');
    Route::get("questions/create/{course}",[QuestionController::class,"create"])->name("questions.create");
})->middleware(["auth","admincheck"]);

Route::prefix("globalworks")->group(function () {
    Route::get("/show/{course}",[QuestionController::class,"show"])->name("question/show")->middleware(["ExamineExpire"]);
    Route::patch("/{question}/update/{examine?}",[QuestionController::class,"globalworksUpdate"])->name("globalworks.update");
    Route::get("/{globalworks}/message",[QuestionController::class,"Message"])->name("globalworks.message");
    Route::post("/message/{globalworks}/store",[QuestionController::class,"messageStore"])->name("globalworks.messageStore");
})->middleware("auth");

Route::prefix("course")->group(function (){
    Route::get("/create",[QuestionController::class,"courseCreate"])->name("course.create")->middleware("admincheck");
    Route::post("/store",[QuestionController::class,"courseStore"])->name("Course.store")->middleware("admincheck");
    Route::get("/{course}/edit",[QuestionController::class,"courseEdit"])->name("course.edit")->middleware("admincheck");
//    Route::get("/{course}/edit/adduser",[QuestionController::class,"addUser"])->name("course.adduser")->middleware("admincheck");
    Route::get("/{course}/exam_create",[QuestionController::class,"examCreate"])->name("exam.create");
    Route::post("/{course}/exam_store",[QuestionController::class,"examStore"])->name("exam.store");
    Route::get("/{exam}/warning",[QuestionController::class,"examWarning"])->name("exam.warning")->middleware("CurrentExamine");
    Route::get("/{exam}/examineCreate",[QuestionController::class,"examineCreate"])->name("examine.start");
    Route::get("/{exam}/results",[QuestionController::class,"examineResults"])->name("examine.results");
    Route::get("/{course}/show",[QuestionController::class,"courseShow"])->name("course.show");
    Route::post("/{course}/join",[QuestionController::class,"courseJoin"])->name("course/join");
    Route::post("/{course}/refresh",[QuestionController::class,"courseRefresh"])->name("course/refresh");
    Route::get("/{course}/{examine}/close",[QuestionController::class,"examineEnd"])->name("examine.end");
    Route::patch("/{course}/open",[QuestionController::class,"courseOpen"])->name("course/open");
})->middleware("auth");

Route::get("dashboard",[QuestionController::class,"dashboard"])->name("dashboard")->middleware("auth");
Route::post("auth",[UsersController::class,"authenticate"])->name("auth");
Route::get("logout",[QuestionController::class,"logout"])->name("logout");
Route::get("admindashboard",[UsersController::class,"admindashboard"])->name("admindashboard")->middleware("auth","admincheck");

Route::resource("questions",QuestionController::class)->except("create","show","store");
Route::resource("users",UsersController::class)->except("create","store")->middleware(["IDcheck"]);

Route::prefix("message")->group(function (){
    Route::get("/{globalworks}/show",[QuestionController::class,"messageShow"])->name("message.show");
    Route::get("/chats",[UsersController::class,"chats"])->name("chats");
});
Route::post("/must",[UsersController::class,"must"]);
