<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Groq
{
    public function suggestSteps(Task $task): ?array
    {
        $key = config('services.groq.key');

        if (blank($key)) {
            return null;
        }

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Devuelve tres primeros pasos concretos, breves y realizables en español para empezar una tarea universitaria. El texto del estudiante describe la tarea. Si indica una fecha de entrega, ajusta los pasos al tiempo que queda.'],
                        ['role' => 'user', 'content' => $this->taskText($task)],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'task_steps',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'steps' => ['type' => 'array', 'items' => ['type' => 'string']],
                                ],
                                'required' => ['steps'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $text = $response->json('choices.0.message.content');

        $steps = is_string($text) ? (json_decode($text, true)['steps'] ?? null) : null;

        if (! is_array($steps) || ! array_is_list($steps) || count($steps) !== 3) {
            return null;
        }

        foreach ($steps as $step) {
            if (! is_string($step) || blank($step)) {
                return null;
            }
        }

        return array_map('trim', $steps);
    }

    private function taskText(Task $task): string
    {
        $text = "{$task->title}\n\n{$task->description}";

        if ($task->due_date) {
            $text .= "\n\nFecha de entrega: {$task->due_date->toDateString()} (hoy es ".today()->toDateString().').';
        }

        return $text;
    }
}
