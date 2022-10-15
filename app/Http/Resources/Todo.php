<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Todo extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'complete' => $this->complete,
            'title' => $this->title,
            'desc' => $this->desc,
            'due_at' => $this->due_at,
            'updated_at' => $this->updated_at,
            'parent_todo_id' => $this->parent_todo_id,
            'sub_tasks' => $this->sub_tasks,
            'meta' => [
                'server_time' => Carbon::now()->toIso8601String()
            ]
        ];
    }
}
