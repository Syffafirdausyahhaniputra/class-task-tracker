<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index(TaskIndexRequest $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $filters = $request->validated();

        // Batasi query sejak database agar setiap peran hanya melihat tugasnya.
        $query = Task::query();

        if ($user->role === 'student') {
            $query->where('class_id', $user->class_id)
                ->with(['completions' => function (Relation $completionQuery) use ($user): void {
                    $completionQuery->where('student_id', $user->id);
                }]);

            if (($filters['status'] ?? null) === 'done') {
                $query->whereHas('completions', function (Builder $completionQuery) use ($user): void {
                    $completionQuery->where('student_id', $user->id);
                });
            } elseif (($filters['status'] ?? null) === 'pending') {
                $query->whereDoesntHave('completions', function (Builder $completionQuery) use ($user): void {
                    $completionQuery->where('student_id', $user->id);
                });
            }
        } else {
            $query->where('created_by', $user->id);
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        $tasks = $query->orderByDesc('due_date')
            ->paginate($filters['per_page'] ?? 10);

        return TaskResource::collection($tasks);
    }

    public function upcomingDeadlines(Request $request): JsonResponse
    {
        $user = $request->user();
        $start = now();
        $end = $start->copy()->addDays(3);

        // Batasi pemberitahuan ke tugas yang boleh dilihat pengguna dan yang belum selesai.
        $query = Task::query()
            ->whereBetween('due_date', [$start, $end])
            ->orderBy('due_date');

        if ($user->role === 'student') {
            $query->where('class_id', $user->class_id)
                ->whereDoesntHave('completions', function (Builder $completionQuery) use ($user): void {
                    $completionQuery->where('student_id', $user->id);
                });
        } else {
            $query->where('created_by', $user->id);
        }

        $tasks = $query->get(['id', 'title', 'due_date']);

        return response()->json([
            'data' => [
                'count' => $tasks->count(),
                'days' => 3,
                'tasks' => $tasks->map(fn ($task): array => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'due_date' => $task->due_date,
                ]),
            ],
        ]);
    }

    public function show(Request $request, Task $task): JsonResource
    {
        Gate::authorize('view', $task);

        if ($request->user()->role === 'student') {
            $task->load(['completions' => function (Relation $query) use ($request): void {
                $query->where('student_id', $request->user()->id);
            }]);
        }

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

    public function complete(CompleteTaskRequest $request, Task $task): JsonResource
    {
        // firstOrCreate menjaga satu penyelesaian per siswa untuk setiap tugas.
        $request->user()->completions()->firstOrCreate(
            ['task_id' => $task->id],
            ['completed_at' => now()],
        );

        $task->load(['completions' => function (Relation $query) use ($request): void {
            $query->where('student_id', $request->user()->id);
        }]);

        return TaskResource::make($task);
    }

    public function uncomplete(CompleteTaskRequest $request, Task $task): Response
    {
        $request->user()->completions()
            ->where('task_id', $task->id)
            ->delete();

        return response()->noContent();
    }
}
