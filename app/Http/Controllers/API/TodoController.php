<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TodoRequest;
use App\Http\Requests\API\TodoUpdateRequest;
use App\Http\Resources\Todo as ResourcesTodo;
use App\Http\Resources\Todos;
use App\Models\Todo;
use App\Repository\TodoRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $filters = request()->query();
        $parents = TodoRepository::getAllParents(request()->user(), $filters)->get();
        foreach ($parents as $parent) {
            $parent->sub_tasks = TodoRepository::getAllChildren($parent)->get()->toArray();
        }
        return response()->json([
            new Todos($parents)
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\API\TodoRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TodoRequest $request)
    {
        try{
        $parent_todo_id = $request->input('parent_todo_id') ?? null;
        if (!is_null($parent_todo_id)) {
            $own = Todo::where('user_id', $request->user()->id)
                ->where('id', $parent_todo_id)
                ->where('parent_todo_id', null)
                ->first();
            if (is_null($own))
                return response()->json([
                    'msg' => 'resource not found'
                ], Response::HTTP_NOT_FOUND);
        }
        $todo = DB::transaction(function () use ($parent_todo_id, $request) {
            $todo = new Todo([
                'user_id' => $request->user()->id,
                'parent_todo_id' => $parent_todo_id,
                'complete' => false,
                'title' => $request->input('title'),
                'desc' => $request->input('desc'),
                'due_at' => $request->input('due_at')
            ]);
            $todo->save();
            return $todo;
        });
        return response()->json([
            new ResourcesTodo($todo)
        ], Response::HTTP_CREATED);
    } // handle internal server error
    catch(\Throwable $th){
        return response()->json([
            'msg' => 'something went wrong'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Todo $todo)
    {
        try {
            $todo = DB::transaction(function () use ($todo) {
                $todo = Todo::where('user_id', request()->user()->id)
                    ->where('id', $todo->id)
                    ->first();
                return $todo;
            });
            if (!is_null($todo))
                return response()->json([
                    'data' => new ResourcesTodo($todo)
                ], Response::HTTP_OK);
            return response()->json([
                'msg' => 'resource not found'
            ], Response::HTTP_NOT_FOUND);
        } // handle internal server error
        catch (\Throwable $th) {
            return response()->json([
                'msg' => 'some error occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TodoUpdateRequest $request, Todo $todo)
    {
        try {
            $updated_todo = DB::transaction(function () use ($todo, $request) {
                $own = is_null(Todo::where('user_id', $request->user()->id)
                    ->where('id', $todo->id)->first()) ? false : true;
                if ($own) {
                    $todo->complete = $request->input('complete') ?? $todo->complete;
                    $todo->title = $request->input('title') ?? $todo->title;
                    $todo->desc = $request->input('desc') ?? $todo->desc;
                    $todo->due_at = $request->input('due_at') ?? $todo->due_at;
                    $todo->update();
                    return $todo;
                }
                return null;
            });
            if (is_null($updated_todo))
                return response()->json([
                    'msg' => 'resource not found'
                ], Response::HTTP_NOT_FOUND);
            return response()->json([
                'msg' => 'todo updated'
            ], Response::HTTP_ACCEPTED);
        } // handle internal server error
        catch (\Throwable $th) {
            return response()->json([
                'msg' => 'something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Todo $todo)
    {
        try {
            $deleted = DB::transaction(function () use ($todo) {
                $own = is_null(Todo::where('user_id', request()->user()->id)
                    ->where('id', $todo->id)->first()) ? false : true;
                if ($own) {
                    $todo->delete();
                    return true;
                }
                return false;
            });
            if ($deleted)
                return response()->json([
                    'msg' => 'todo successfully deleted'
                ], Response::HTTP_ACCEPTED);
            return response()->json([
                'msg' => 'resource not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $th) {
            return response()->json([
                'msg' => 'something went wrong'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
