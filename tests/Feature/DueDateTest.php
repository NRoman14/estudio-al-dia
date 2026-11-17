<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DueDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-16 10:00:00');
    }

    public function test_a_task_can_be_saved_with_and_without_a_due_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('tasks.create'))->assertOk()->assertSee('Fecha de entrega');

        $this->post(route('tasks.store'), ['title' => 'Quiz', 'description' => 'Repasar.', 'due_date' => '2026-11-17'])
            ->assertSessionHasNoErrors();
        $this->post(route('tasks.store'), ['title' => 'Lectura', 'description' => 'Capítulo 4.', 'due_date' => ''])
            ->assertSessionHasNoErrors();

        $quiz = $user->tasks()->where('title', 'Quiz')->sole();
        $this->assertSame('2026-11-17', $quiz->due_date->toDateString());
        $this->assertNull($user->tasks()->where('title', 'Lectura')->sole()->due_date);

        $this->get(route('tasks.show', $quiz))->assertSee('Vence mañana');
        $this->get(route('tasks.edit', $quiz))->assertSee('value="2026-11-17"', false);
    }

    public function test_an_invalid_due_date_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), ['title' => 'Quiz', 'description' => 'Repasar.', 'due_date' => '17/11/2026'])
            ->assertSessionHasErrors(['due_date' => 'Ingresa una fecha de entrega válida.']);
    }

    public function test_the_list_orders_pending_tasks_by_due_date_and_flags_overdue_ones(): void
    {
        $user = User::factory()->create();
        $user->tasks()->create(['title' => 'Sin fecha', 'description' => '-']);
        $user->tasks()->create(['title' => 'En cinco días', 'description' => '-', 'due_date' => '2026-11-21']);
        $user->tasks()->create(['title' => 'Atrasada', 'description' => '-', 'due_date' => '2026-11-14']);
        $user->tasks()->create(['title' => 'Para hoy', 'description' => '-', 'due_date' => '2026-11-16']);

        $this->actingAs($user)->get(route('tasks.index'))
            ->assertOk()
            ->assertSeeInOrder(['Atrasada', 'Para hoy', 'En cinco días', 'Sin fecha'])
            ->assertSee('Atrasada · venció el 14 de noviembre')
            ->assertSee('Vence hoy')
            ->assertSee('Vence en 5 días')
            ->assertSee('Tienes 1 tarea atrasada');
    }

    public function test_a_completed_task_is_not_overdue(): void
    {
        $user = User::factory()->create();
        $task = $user->tasks()->create(['title' => 'Ensayo', 'description' => '-', 'due_date' => '2026-11-10']);
        $task->completed = true;
        $task->save();

        $this->assertFalse($task->isOverdue());
        $this->actingAs($user)->get(route('tasks.index'))
            ->assertSee('Entrega: 10 de noviembre')
            ->assertDontSee('tarea atrasada');
    }

    public function test_changing_the_due_date_clears_steps_and_groq_receives_the_date(): void
    {
        $user = User::factory()->create();
        $task = $user->tasks()->create(['title' => 'Exposición', 'description' => 'Buscar ejemplos.', 'due_date' => '2026-11-20']);
        $task->steps = ['Uno.', 'Dos.', 'Tres.'];
        $task->save();
        $this->actingAs($user);

        $this->put(route('tasks.update', $task), ['title' => 'Exposición', 'description' => 'Buscar ejemplos.', 'due_date' => '2026-11-20']);
        $this->assertNotNull($task->fresh()->steps);

        $this->put(route('tasks.update', $task), ['title' => 'Exposición', 'description' => 'Buscar ejemplos.', 'due_date' => '2026-11-18']);
        $this->assertNull($task->fresh()->steps);

        config(['services.groq.key' => 'test-key']);
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => json_encode(['steps' => ['A.', 'B.', 'C.']])]]]])]);

        $this->post(route('tasks.suggest', $task))->assertRedirect(route('tasks.show', $task).'#sugerencias');
        $this->assertSame(['A.', 'B.', 'C.'], $task->fresh()->steps);
        Http::assertSent(fn (Request $request) => $request['messages'][1]['content']
            === "Exposición\n\nBuscar ejemplos.\n\nFecha de entrega: 2026-11-18 (hoy es 2026-11-16).");
    }
}
