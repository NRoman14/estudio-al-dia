@extends('layouts.app')
@section('title', $registering ? 'Crear cuenta' : 'Iniciar sesión')

@section('content')
    <main class="auth">
        <x-brand :href="route('login')" />

        <div class="intro">
            <span class="eyebrow">Comienza aquí</span>
            <h1>{{ $registering ? 'Crea tu cuenta' : 'Qué bueno verte de nuevo' }}</h1>
            <p>{{ $registering ? 'Empieza a organizar tu estudio en pocos segundos.' : 'Ingresa para continuar con tus tareas.' }}</p>
        </div>

        <nav class="auth-tabs" aria-label="Acceso a la cuenta">
            <a href="{{ route('login') }}" @if(!$registering) aria-current="page" @endif>Iniciar sesión</a>
            <a href="{{ route('register') }}" @if($registering) aria-current="page" @endif>Crear cuenta</a>
        </nav>

        <x-status />
        <form class="fields" method="POST" action="{{ route($registering ? 'register.store' : 'login.store') }}">
            @csrf
            @if($registering)
                <div class="field">
                    <label for="name">Nombre</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Ej. Lucía Fernández" maxlength="100" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name') <small class="field-error" id="name-error">{{ $message }}</small> @enderror
                </div>
            @endif
            <div class="field">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="lucia@universidad.edu" maxlength="254" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email') <small class="field-error" id="email-error">{{ $message }}</small> @enderror
            </div>
            <div class="field">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" autocomplete="{{ $registering ? 'new-password' : 'current-password' }}" placeholder="••••••••••" required @if($registering) minlength="8" @endif @error('password') aria-invalid="true" aria-describedby="password-error" @else @if($registering) aria-describedby="password-hint" @endif @enderror>
                @error('password') <small class="field-error" id="password-error">{{ $message }}</small> @enderror
                @if($registering)
                    <small id="password-hint">Usa 8 caracteres como mínimo.</small>
                @endif
            </div>
            <button class="button primary">{{ $registering ? 'Crear mi cuenta' : 'Entrar a mis tareas' }}</button>
        </form>

        <p class="auth-footnote">Al continuar, aceptas usar Estudio al Día para fines académicos.</p>
    </main>
@endsection
