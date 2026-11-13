@extends('layouts.app')
@section('title', 'Mis tareas')

@section('content')
    <main class="shell page">
        <div class="page-heading">
            <div class="intro">
                <span class="eyebrow">Hola, {{ str(auth()->user()->name)->before(' ') }}</span>
                <h1>Mis tareas</h1>
                <p>{{ $pending->count() }} {{ $pending->count() === 1 ? 'tarea pendiente' : 'tareas pendientes' }}</p>
            </div>
            <a class="button primary" href="{{ route('tasks.create') }}"><x-icon name="plus" /> Nueva tarea</a>
        </div>

        <x-status />
        <section class="focus-panel" aria-labelledby="focus-title">
            <span class="focus-icon"><x-icon name="sparkles" /></span>
            <div class="focus-copy">
                <h2 id="focus-title">Un paso a la vez</h2>
                <p>
                    @if($pending->isEmpty())
                        {{ $completed->isEmpty() ? 'Agrega una tarea para empezar a organizar tu estudio.' : 'Estás al día con tus tareas.' }}
                    @elseif($pending->count() === 1)
                        Dedica 20 minutos a tu tarea pendiente.
                    @else
                        Elige una de tus {{ $pending->count() }} tareas pendientes y avanza durante 20 minutos.
                    @endif
                </p>
            </div>
            <div class="focus-count" aria-label="{{ $completed->count() }} tareas completadas">
                <strong>{{ $completed->count() }}</strong>
                <span>{{ $completed->count() === 1 ? 'hecha' : 'hechas' }}</span>
            </div>
        </section>

        @foreach(['Pendientes' => $pending, 'Completadas' => $completed] as $heading => $tasks)
            <section class="task-group" aria-labelledby="group-{{ $loop->index }}">
                <div class="group-heading">
                    <h2 id="group-{{ $loop->index }}">{{ $heading }}</h2>
                    <span class="count">{{ $tasks->count() }}</span>
                </div>
                <ul class="task-list">
                    @forelse($tasks as $task)
                        <li @class(['task-row', 'completed' => $task->completed])>
                            <form method="POST" action="{{ route('tasks.completion', $task) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="completed" value="{{ $task->completed ? 0 : 1 }}">
                                <input type="hidden" name="from_list" value="1">
                                <button class="task-toggle" aria-label="{{ $task->completed ? 'Volver a pendiente' : 'Marcar como completada' }}: {{ $task->title }}">
                                    @if($task->completed)<x-icon name="check" />@endif
                                </button>
                            </form>
                            <a class="task-link" href="{{ route('tasks.show', $task) }}">
                                <span>
                                    <strong>{{ $task->title }}</strong>
                                    <span class="task-preview">{{ $task->description }}</span>
                                </span>
                                <x-icon name="next" />
                            </a>
                        </li>
                    @empty
                        <li class="empty">{{ $heading === 'Pendientes' ? 'No tienes tareas pendientes.' : 'Todavía no tienes tareas completadas.' }}</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </main>
@endsection
