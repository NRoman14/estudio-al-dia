<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'due_date'];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'due_date' => 'date',
            'steps' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOverdue(): bool
    {
        return ! $this->completed && $this->due_date?->lt(today());
    }

    /**
     * Texto y tono del aviso de entrega, o null si la tarea no tiene fecha.
     *
     * @return array{label: string, tone: string}|null
     */
    public function dueStatus(): ?array
    {
        if ($this->due_date === null) {
            return null;
        }

        $date = $this->due_date->translatedFormat('j \d\e F');

        if ($this->completed) {
            return ['label' => "Entrega: {$date}", 'tone' => 'muted'];
        }

        $days = (int) today()->diffInDays($this->due_date, false);

        return match (true) {
            $days < 0 => ['label' => "Atrasada · venció el {$date}", 'tone' => 'late'],
            $days === 0 => ['label' => 'Vence hoy', 'tone' => 'soon'],
            $days === 1 => ['label' => 'Vence mañana', 'tone' => 'soon'],
            $days <= 7 => ['label' => "Vence en {$days} días", 'tone' => 'normal'],
            default => ['label' => "Entrega: {$date}", 'tone' => 'normal'],
        };
    }
}
