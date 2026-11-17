<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProgramApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_sanctum_token(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token']);

        $token = $response->json('access_token');

        $this->withToken($token)
            ->getJson('/api/programs')
            ->assertOk();

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);

        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/programs')
            ->assertUnauthorized();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_program_api_requires_authentication(): void
    {
        $this->getJson('/api/programs')->assertUnauthorized();
    }

    public function test_authenticated_user_can_list_and_show_programs(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $program = Program::create([
            'nama' => 'Pendidikan',
            'deskripsi' => 'Program pendidikan.',
        ]);

        $this->getJson('/api/programs')
            ->assertOk()
            ->assertJsonPath('data.0.id', $program->id)
            ->assertJsonPath('data.0.nama', 'Pendidikan');

        $this->getJson('/api/programs/'.$program->id)
            ->assertOk()
            ->assertJsonPath('data.deskripsi', 'Program pendidikan.');
    }

    public function test_authenticated_user_can_create_update_and_delete_programs(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/programs', [
            'nama' => 'Kesehatan',
            'deskripsi' => 'Program layanan kesehatan.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.nama', 'Kesehatan');

        $program = Program::query()->where('nama', 'Kesehatan')->firstOrFail();

        $this->putJson('/api/programs/'.$program->id, [
            'nama' => 'Kesehatan Terpadu',
            'deskripsi' => 'Program kesehatan terpadu.',
        ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Kesehatan Terpadu');

        $this->patchJson('/api/programs/'.$program->id, [
            'deskripsi' => 'Program kesehatan terpadu yang diperbarui.',
        ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Kesehatan Terpadu')
            ->assertJsonPath('data.deskripsi', 'Program kesehatan terpadu yang diperbarui.');

        $this->deleteJson('/api/programs/'.$program->id)
            ->assertOk()
            ->assertJsonPath('message', 'Program berhasil dihapus.');

        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
    }

    public function test_program_validation_rejects_missing_fields_and_long_names(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/programs', [
            'nama' => str_repeat('a', 101),
            'deskripsi' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nama', 'deskripsi']);
    }
}
