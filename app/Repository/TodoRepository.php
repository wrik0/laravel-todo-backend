<?php

declare(strict_types=1);

namespace App\Repository;

use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;

class TodoRepository
{
    /**
     * @var array $timeDeltas
     */
    private static $timeDeltas = [
        'today',
        'this_week',
        'next_week',
        'overdue',
    ];
    public static function getAllParents(User $user, array $filters)
    {
        $query = Todo::where('user_id', $user->id);
        $due = $filters['due'] ?? null;
        if (!is_null($due) && in_array($due, self::$timeDeltas)) {
            $query = $query->where('due_at', '<=', self::computeTime($due));
        }
        $title = $filters['title'] ?? null;
        if (!is_null($title)) {
            $query = $query->where('title', 'like', '%' . $title . '%');
        }
        $query = $query->where('parent_todo_id', null)
            ->orderBy('due_at', 'ASC');

        return $query;
    }
    public static function getAllChildren(Todo $todo)
    {
        return Todo::where('parent_todo_id', $todo->id)
            ->orderBy('due_at', 'ASC');
    }
    private static function computeTime(string $timeDelta)
    {
        switch($timeDelta){
            case 'today':
                return Carbon::today()->toDateTimeString();
            case 'this_week':
                return Carbon::now()->endOfWeek()->toDateTimeString();
            case 'next_week':
                return Carbon::now()->addWeek()->endOfWeek()->toDateTimeString();
            case 'overdue':
                return Carbon::now()->toDateTimeString();
        }
    }
}
