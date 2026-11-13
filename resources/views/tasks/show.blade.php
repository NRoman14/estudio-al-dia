@extends('layouts.app')
@section('title', $task->title)

@section('content')
    <main class="shell page">
        <a class="back-link" href="{{ route('tasks.index') }}"><x-icon name="back" /> Mis tareas</a>
        <x-status />

        <article class="panel detail">
            <div class="detail-body">
                <div class="detail-meta">
                    <span @class(['task-status', 'done' => $task->completed])>{{ $task->completed ? 'Completada' : 'Pendiente' }}</span>
                    <a class="button secondary" href="{{ route('tasks.edit', $task) }}"><x-icon name="edit" /> Editar</a>
                </div>
                <h1>{{ $task->title }}</h1>
                <div class="description">
                    <h2>Descripción</h2>
                    <p>{{ $task->description }}</p>
                </div>
            </div>

            <section id="sugerencias" class="suggestion" aria-labelledby="suggestion-title">
                <span class="brand-mark"><x-icon name="sparkles" /></span>
                <div>
                    <h2 id="suggestion-title">¿No sabes por dónde empezar?</h2>
                    <p>Groq puede convertir esta tarea en tres primeros pasos concretos.</p>
                    @if($task->steps)
                        <ol class="steps">
                            @foreach($task->steps as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                    @endif
                    @if(session('suggestion_error'))
                        <p class="notice warning" role="alert">{{ session('suggestion_error') }}</p>
                    @endif
                    <form method="POST" action="{{ route('tasks.suggest', $task) }}" data-loading>
                        @csrf
                        <button class="button primary"><x-icon name="sparkles" /> <span>{{ $task->steps ? 'Volver a sugerir pasos' : 'Sugerir pasos' }}</span></button>
                    </form>
                </div>
            </section>

            <div class="detail-footer">
                <button class="button danger-link" data-dialog="delete-task"><x-icon name="trash" /> Eliminar tarea</button>
                <form method="POST" action="{{ route('tasks.completion', $task) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="completed" value="{{ $task->completed ? 0 : 1 }}">
                    <button class="button {{ $task->completed ? 'secondary' : 'primary' }}">
                        <x-icon :name="$task->completed ? 'undo' : 'circle-check'" />
                        {{ $task->completed ? 'Volver a pendiente' : 'Marcar como completada' }}
                    </button>
                </form>
            </div>
        </article>

        <x-confirmation id="delete-task" title="¿Eliminar esta tarea?" description="La tarea se eliminará definitivamente." :action="route('tasks.destroy', $task)" confirm="Sí, eliminar tarea" />
    </main>
@endsection
