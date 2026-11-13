@props(['id', 'title', 'description', 'action', 'confirm'])

<dialog id="{{ $id }}" class="confirmation" aria-labelledby="{{ $id }}-title" aria-describedby="{{ $id }}-description">
    <div class="dialog-top">
        <span class="danger-icon"><x-icon name="trash" /></span>
        <form method="dialog">
            <button class="icon-button" aria-label="Cerrar"><x-icon name="close" /></button>
        </form>
    </div>
    <h2 id="{{ $id }}-title">{{ $title }}</h2>
    <p id="{{ $id }}-description">{{ $description }}</p>
    <div class="actions">
        <form method="dialog"><button class="button secondary" autofocus>Cancelar</button></form>
        <form method="POST" action="{{ $action }}">
            @csrf
            @method('DELETE')
            <input type="hidden" name="confirmed" value="1">
            <button class="button danger">{{ $confirm }}</button>
        </form>
    </div>
</dialog>
