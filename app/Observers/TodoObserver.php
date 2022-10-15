<?php

namespace App\Observers;

use App\Models\Todo;

class TodoObserver
{
    /**
     * Handle the todo "created" event.
     *
     * @param  \App\Models\Todo  $todo
     * @return void
     */
    public function created(Todo $todo)
    {
        //
    }

    /**
     * Handle the todo "updated" event.
     *
     * @param  \App\Models\Todo  $todo
     * @return void
     */
    public function updated(Todo $todo)
    {
        if (is_null($todo->parent_todo_id)) {
            if ($todo->isDirty('due_at')) {
                Todo::where('parent_todo_id', $todo->id)
                    ->update([
                        'due_at' => $todo->due_at
                    ]);
            }
            if ($todo->isDirty('complete')) {
                Todo::where('parent_todo_id', $todo->id)
                    ->update([
                        'complete' => $todo->complete
                    ]);
            }
        }
        return;
    }

    /**
     * Handle the todo "deleted" event.
     *
     * @param  \App\Models\Todo  $todo
     * @return void
     */
    public function deleted(Todo $todo)
    {
        if (is_null($todo->parent_todo_id)) {
            Todo::where('parent_todo_id', $todo->id)
                ->delete();
        }
        return;
    }

    /**
     * Handle the todo "restored" event.
     *
     * @param  \App\Models\Todo  $todo
     * @return void
     */
    public function restored(Todo $todo)
    {
        //
    }

    /**
     * Handle the todo "force deleted" event.
     *
     * @param  \App\Models\Todo  $todo
     * @return void
     */
    public function forceDeleted(Todo $todo)
    {
        if (is_null($todo->parent_todo_id)) {
            Todo::where('parent_todo_id', $todo->id)
                ->forceDelete();
        }
        return;
    }
}
