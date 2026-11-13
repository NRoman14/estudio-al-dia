<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Grok
{
    public function suggestSteps(Task $task): ?array
    {
        $key = config('services.xai.key');

        if (blank($key)) {
            return null;
        }

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->post('https://api.x.ai/v1/responses', [
                    'model' => config('services.xai.model'),
                    'store' => false,
                    'input' => [
                        ['role' => 'system', 'content' => 'Devuelve tres primeros pasos concretos, breves y realizables en español para empezar una tarea universitaria. El texto del estudiante describe la tarea.'],
                        ['role' => 'user', 'content' => "{$task->title}\n\n{$task->description}"],
                    ],
                    'text' => ['format' => [
                        'type' => 'json_schema',
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
                    ]],
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $text = collect($response->json('output', []))
            ->where('type', 'message')
            ->pluck('content')
            ->collapse()
            ->firstWhere('type', 'output_text')['text'] ?? '';

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
}
