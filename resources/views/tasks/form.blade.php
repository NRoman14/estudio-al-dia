@extends('layouts.app')
@php
    $editing = $task->exists;
    $title = $editing ? 'Editar tarea' : 'Nueva tarea';
    $back = $editing ? route('tasks.show', $task) : route('tasks.index');
    $taskTitle = old('title', $task->title);
    $description = old('description', $task->description);
@endphp
@section('title', $title)

@section('content')
    <main class="shell narrow page">
        <a class="back-link" href="{{ $back }}"><x-icon name="back" /> Volver</a>

        <div class="intro">
            <span class="eyebrow">{{ $editing ? 'Editar' : 'Organiza tu semana' }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $editing ? 'Actualiza la información para mantener tu plan al día.' : 'Anota qué necesitas hacer. Podrás pedir ayuda para empezar después.' }}</p>
        </div>

        <form class="panel padded fields" method="POST" action="{{ $editing ? route('tasks.update', $task) : route('tasks.store') }}">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="field">
                <label for="title">Título</label>
                <input id="title" name="title" maxlength="90" value="{{ $taskTitle }}" placeholder="Ej. Preparar exposición de Base de Datos" aria-describedby="title-count @error('title') title-error @enderror" required autofocus @error('title') aria-invalid="true" @enderror>
                <small id="title-count"><span data-count="title">{{ mb_strlen((string) $taskTitle) }}</span>/90 caracteres</small>
                @error('title') <small class="field-error" id="title-error">{{ $message }}</small> @enderror
            </div>
            <div class="field">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="6" maxlength="500" placeholder="Agrega contexto, indicaciones o una fecha importante..." aria-describedby="description-count @error('description') description-error @enderror" required @error('description') aria-invalid="true" @enderror>{{ $description }}</textarea>
                <small id="description-count"><span data-count="description">{{ mb_strlen((string) $description) }}</span>/500 caracteres</small>
                @error('description') <small class="field-error" id="description-error">{{ $message }}</small> @enderror
            </div>
            <div class="actions">
                <a class="button secondary" href="{{ $back }}">Cancelar</a>
                <button class="button primary">Guardar tarea</button>
            </div>
        </form>
    </main>
@endsection
