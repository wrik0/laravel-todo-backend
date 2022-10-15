<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->bigInteger('id')->autoIncrement();
            $table->bigInteger('user_id');
            $table->bigInteger('parent_todo_id')->nullable();
            $table->boolean('complete');
            $table->string('title', 255);
            $table->text('desc')->nullable();
            $table->softDeletes();
            $table->timestamps();
            // fk user_id -> users(id)
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['user_id', 'complete']);
        });
        Schema::table('todos', function (Blueprint $table) {
            // fk parent_todo -> todos(id)
            $table->foreign('parent_todo_id')->references('id')->on('todos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('todos', function(Blueprint $table) {
            $table->dropForeign('todos_parent_todo_id_foreign');
            $table->dropForeign('todos_user_id_complete_index');
        });
        Schema::dropIfExists('todos');
    }
}
