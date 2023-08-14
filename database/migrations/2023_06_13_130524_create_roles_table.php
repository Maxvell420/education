<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string('secret')->nullable();
        });
        DB::table("roles")->insert(["name"=>"user"]);
        DB::table("roles")->insert(["name"=>"admin"]);
        DB::table("roles")->insert(["name"=>"bot",'secret'=>"i am admin!"]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
