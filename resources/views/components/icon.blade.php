@props(['name'])

<svg {{ $attributes->class('icon') }} width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('list') <path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('back') <path d="m15 18-6-6 6-6"/> @break
        @case('next') <path d="m9 18 6-6-6-6"/> @break
        @case('sparkles') <path d="m14 2 2.2 5.8L22 10l-5.8 2.2L14 18l-2.2-5.8L6 10l5.8-2.2L14 2ZM5 14l1.2 2.8L9 18l-2.8 1.2L5 22l-1.2-2.8L1 18l2.8-1.2L5 14Z"/> @break
        @case('edit') <path d="m13.5 6.5 4 4M4 20l4.5-1 11-11a2.8 2.8 0 0 0-4-4l-11 11L4 20Zm8 0h8"/> @break
        @case('trash') <path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5m4-5v5"/> @break
        @case('logout') <path d="M13 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5M4 12h11m-4-4 4 4-4 4"/> @break
        @case('close') <path d="m6 6 12 12M18 6 6 18"/> @break
        @case('circle-check') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
        @case('calendar') <rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/> @break
        @case('undo') <path d="M9 7 4 12l5 5M4 12h12a4 4 0 0 1 4 4"/> @break
    @endswitch
</svg>
