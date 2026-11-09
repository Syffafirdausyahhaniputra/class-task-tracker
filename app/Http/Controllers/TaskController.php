<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function show(Request $request, Task $task): JsonResource
    {
        Gate::authorize('view', $task);

        return TaskResource::make($task);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        // Relasi mengisi created_by dari guru yang login, bukan dari input.
        $task = $request->user()->createdTasks()->create($request->validated());

        return TaskResource::make($task)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResource
    {
        $task->fill($request->validated());
        $task->save();

        return TaskResource::make($task);
    }

    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
