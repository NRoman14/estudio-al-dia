@props(['task'])

@if($status = $task->dueStatus())
    <span {{ $attributes->class(['due', 'due-'.$status['tone']]) }}>
        <x-icon name="calendar" /> {{ $status['label'] }}
    </span>
@endif
