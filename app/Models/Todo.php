<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model
{
    use SoftDeletes;
    /**
     * @var string 
     */
    protected $table = 'todos';
    protected $guarded = [];
    protected $casts = [
        'completed' => 'boolean',
        'due_at' => 'datetime'
    ];
    // protected $dates = [
    //     'due_at'
    // ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function subTodos()
    {
        return $this->hasMany(Todo::class, 'parent_todo_id', 'id');
    }
    public function parentTodo()
    {
        return Todo::where('id', $this->parent_todo_id)->first();
    }
    public function setDueAtAttribute($value){
        $date = new Carbon($value);
        $this->attributes['due_at'] = $date->toDateTimeString();
    }
    public function getSubTasksAttribute(){
        return Todo::where('parent_todo_id', $this->id)->get();
    }
}
