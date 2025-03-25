<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller {
    public function index(Request $request) {
        $tasks = Task::query();

        if ($request->has('status')) {
            $tasks->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $tasks->where('priority', $request->priority);
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $tasks->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }
        if ($request->has('sort_by')) {
            $tasks->orderBy($request->sort_by, $request->get('order', 'asc'));
        }

        return TaskResource::collection($tasks->paginate(10));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|unique:tasks',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date' => 'required|date|after:today',
            'user_id' => 'required|exists:users,id',
        ]);

        $task = Task::create($validated);

        return new TaskResource($task);
    }

    public function update(Request $request, Task $task) {
        $validated = $request->validate([
            'title' => 'sometimes|required|unique:tasks,title,' . $task->id,
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'due_date' => 'sometimes|required|date|after:today',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $task->update($validated);

        return new TaskResource($task);
    }

    public function destroy(Task $task) {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    public function trashed() {
        return TaskResource::collection(Task::onlyTrashed()->get());
    }

    public function restore($id) {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();
        return new TaskResource($task);
    }
    
}

