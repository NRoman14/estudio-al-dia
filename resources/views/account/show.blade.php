@extends('layouts.app')
@section('title', 'Mi cuenta')

@section('content')
    <main class="shell narrow page">
        <div class="intro">
            <span class="eyebrow">Configuración</span>
            <h1>Mi cuenta</h1>
            <p>Administra tu sesión y los datos asociados a tu perfil.</p>
        </div>

        <section class="panel padded">
            <div class="profile">
                <span class="avatar large" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <div>
                    <h2>{{ auth()->user()->name }}</h2>
                    <p>{{ auth()->user()->email }}</p>
                </div>
            </div>
            <div class="session">
                <h3>Sesión</h3>
                <p>Cierra tu sesión en este dispositivo.</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button secondary"><x-icon name="logout" /> Cerrar sesión</button>
                </form>
            </div>
        </section>

        <section class="panel padded danger-panel">
            <h2>Zona de peligro</h2>
            <p>Eliminar tu cuenta es permanente. También se borrarán todas tus tareas y no podrás recuperarlas.</p>
            @error('confirmed') <p class="field-error" role="alert">{{ $message }}</p> @enderror
            <button class="button danger" data-dialog="delete-account"><x-icon name="trash" /> Eliminar cuenta</button>
        </section>

        <x-confirmation id="delete-account" title="¿Eliminar tu cuenta?" description="Se eliminarán definitivamente tu cuenta y todas tus tareas, tanto pendientes como completadas." :action="route('account.destroy')" confirm="Sí, eliminar mi cuenta" />
    </main>
@endsection
