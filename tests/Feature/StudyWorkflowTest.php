<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_manage_tasks_and_delete_their_account(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Qué bueno verte de nuevo');
        $this->get(route('register'))->assertOk()->assertSee('Crea tu cuenta');
        $this->get(route('tasks.index'))->assertRedirect(route('login'));

        $credentials = ['email' => 'lucia@example.test', 'password' => 'clave-de-prueba'];
        $this->post(route('register.store'), ['name' => 'Lucía', ...$credentials])
            ->assertRedirect(route('tasks.index'));

        $user = User::where('email', $credentials['email'])->sole();
        $other = User::factory()->create();
        $otherTask = $other->tasks()->create(['title' => 'Otra tarea', 'description' => 'Otro dueño']);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('No tienes tareas pendientes.')
            ->assertDontSee($otherTask->title);
        $this->get(route('tasks.create'))->assertOk()->assertSee('Nueva tarea');

        $this->post(route('tasks.store'), ['title' => '   ', 'description' => ''])
            ->assertSessionHasErrors(['title', 'description']);
        $this->post(route('tasks.store'), ['title' => 'Preparar exposición', 'description' => 'Buscar ejemplos.'])
            ->assertSessionHasNoErrors();
        $task = $user->tasks()->sole();

        $this->get(route('tasks.edit', $task))->assertOk()->assertSee($task->title);
        $this->put(route('tasks.update', $task), ['title' => 'Editar exposición', 'description' => 'Preparar tres ejemplos.'])
            ->assertRedirect(route('tasks.show', $task));
        $this->get(route('tasks.show', $task))->assertOk()->assertSee('Editar exposición');

        $this->patch(route('tasks.completion', $task), ['completed' => '1', 'from_list' => '1'])
            ->assertRedirect(route('tasks.index'));
        $this->assertTrue($task->fresh()->completed);
        $this->patch(route('tasks.completion', $task), ['completed' => '0'])
            ->assertRedirect(route('tasks.show', $task));
        $this->assertFalse($task->fresh()->completed);

        $extraTask = $user->tasks()->create(['title' => 'Tarea temporal', 'description' => 'Para eliminar']);
        $this->delete(route('tasks.destroy', $extraTask), ['confirmed' => '1'])->assertRedirect(route('tasks.index'));
        $this->assertModelMissing($extraTask);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->post(route('login.store'), $credentials)->assertRedirect(route('tasks.index'));
        $this->get(route('account.show'))->assertOk()->assertSee('¿Eliminar tu cuenta?');

        $this->from(route('account.show'))->delete(route('account.destroy'))
            ->assertRedirect(route('account.show'))
            ->assertSessionHasErrors('confirmed');
        $this->assertModelExists($user);

        $this->delete(route('account.destroy'), ['confirmed' => '1'])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($task);
        $this->assertModelExists($otherTask);
    }

    public function test_a_second_student_cannot_access_or_modify_the_first_students_task(): void
    {
        Http::fake();
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $task = $owner->tasks()->create(['title' => 'Tarea privada', 'description' => 'Texto privado']);

        $this->actingAs($visitor);
        $this->get(route('tasks.index'))->assertOk()->assertDontSee($task->title);
        $this->get(route('tasks.show', $task))->assertForbidden();
        $this->get(route('tasks.edit', $task))->assertForbidden();
        $this->put(route('tasks.update', $task), ['title' => 'Cambio', 'description' => 'Cambio'])
            ->assertForbidden();
        $this->patch(route('tasks.completion', $task), ['completed' => '1'])->assertForbidden();
        $this->post(route('tasks.suggest', $task))->assertForbidden();
        $this->delete(route('tasks.destroy', $task))->assertForbidden();

        Http::assertNothingSent();
        $this->assertModelExists($task);
    }

    public function test_grok_suggestions_only_send_task_text_and_handle_unavailable_service(): void
    {
        $user = User::factory()->create();
        $task = $user->tasks()->create(['title' => 'Exposición', 'description' => 'Buscar ejemplos.']);
        $url = 'https://api.x.ai/v1/responses';
        $steps = ['Elige el tema central.', 'Busca dos ejemplos.', 'Esboza una diapositiva.'];
        config(['services.xai.key' => 'test-key']);

        Http::fake([
            $url => Http::sequence()
                ->push([
                    'output' => [
                        ['type' => 'reasoning'],
                        ['type' => 'message', 'content' => [
                            ['type' => 'output_text', 'text' => json_encode(['steps' => $steps])],
                        ]],
                    ],
                ])
                ->pushStatus(429),
        ]);

        $this->actingAs($user)->post(route('tasks.suggest', $task))
            ->assertRedirect(route('tasks.show', $task).'#sugerencias');
        $this->assertSame($steps, $task->fresh()->steps);
        $this->get(route('tasks.show', $task))->assertSeeInOrder($steps);

        Http::assertSent(fn (Request $request) => $request->url() === $url
            && $request['store'] === false
            && $request['input'][1] === ['role' => 'user', 'content' => "Exposición\n\nBuscar ejemplos."]
            && ! str_contains($request->body(), $user->email));

        $this->post(route('tasks.suggest', $task))->assertSessionHas('suggestion_error');
        $this->get(route('tasks.show', $task))->assertOk()->assertSee('No pudimos sugerir pasos ahora');
        $this->assertSame($steps, $task->fresh()->steps);

        $this->put(route('tasks.update', $task), ['title' => $task->title, 'description' => 'Otro enfoque.']);
        $this->assertNull($task->fresh()->steps);

        config(['services.xai.key' => null]);
        $this->post(route('tasks.suggest', $task))->assertSessionHas('suggestion_error');
        Http::assertSentCount(2);
    }
}
