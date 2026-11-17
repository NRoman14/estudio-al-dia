<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\Groq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        // Pendientes: primero las que vencen antes; las que no tienen fecha, al final.
        $tasks = $request->user()->tasks()
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest('id')
            ->get();
        [$completed, $pending] = $tasks->partition('completed');
        $completed = $completed->sortByDesc('id');
        $overdue = $pending->filter->isOverdue()->count();

        return view('tasks.index', compact('completed', 'pending', 'overdue'));
    }

    public function create(): View
    {
        return view('tasks.form', ['task' => new Task]);
    }

    public function store(Request $request): RedirectResponse
    {
        $task = $request->user()->tasks()->create($this->validateTask($request));

        return to_route('tasks.show', $task)->with('status', 'Tarea guardada.');
    }

    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        Gate::authorize('update', $task);

        return view('tasks.form', compact('task'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $task->fill($this->validateTask($request));

        if ($task->isDirty(['title', 'description', 'due_date'])) {
            $task->steps = null;
        }

        $task->save();

        return to_route('tasks.show', $task)->with('status', 'Tarea actualizada.');
    }

    public function completion(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $request->validate(['completed' => ['required', 'boolean'], 'from_list' => ['sometimes', 'boolean']]);
        $task->completed = $request->boolean('completed');
        $task->save();

        return $request->boolean('from_list')
            ? to_route('tasks.index')
            : to_route('tasks.show', $task);
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $request->validate(['confirmed' => ['accepted']]);
        $task->delete();

        return to_route('tasks.index')->with('status', 'Tarea eliminada.');
    }

    public function suggest(Task $task, Groq $groq): RedirectResponse
    {
        Gate::authorize('update', $task);

        $steps = $groq->suggestSteps($task);

        if ($steps === null) {
            return to_route('tasks.show', $task)->with('suggestion_error', 'No pudimos sugerir pasos ahora. Puedes seguir gestionando tus tareas e intentarlo más tarde.');
        }

        // An edit may have changed the text while Groq was generating the steps.
        $saved = Task::query()
            ->whereKey($task->id)
            ->where('title', $task->title)
            ->where('description', $task->description)
            ->where('due_date', $task->getRawOriginal('due_date'))
            ->update(['steps' => json_encode($steps, JSON_THROW_ON_ERROR)]);

        if (! $saved) {
            return to_route('tasks.index')->with('status', 'La tarea cambió mientras se generaban los pasos.');
        }

        return redirect()->to(route('tasks.show', $task).'#sugerencias');
    }

    private function validateTask(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:90'],
            'description' => ['required', 'string', 'max:500'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ]);
    }
}
