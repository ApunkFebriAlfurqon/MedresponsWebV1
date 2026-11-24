<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_a_jwt_token(): void
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
        $payload = JWT::decode($token, new Key($this->jwtSecret(), 'HS256'));

        $this->assertSame($user->id, $payload->sub);

        $this->withToken($token)
            ->getJson('/api/programs')
            ->assertOk();

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->withToken('invalid-token')
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
        $this->withToken($this->jwtForUser(User::factory()->create()));

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
        $this->withToken($this->jwtForUser(User::factory()->create()));

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
        $this->withToken($this->jwtForUser(User::factory()->create()));

        $this->postJson('/api/programs', [
            'nama' => str_repeat('a', 101),
            'deskripsi' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nama', 'deskripsi']);
    }

    public function test_all_program_endpoints_reject_missing_jwt(): void
    {
        $program = Program::create([
            'nama' => 'Program Uji JWT',
            'deskripsi' => 'Digunakan untuk pengujian autentikasi.',
        ]);

        $this->getJson('/api/programs')->assertUnauthorized();
        $this->postJson('/api/programs', [])->assertUnauthorized();
        $this->getJson('/api/programs/'.$program->id)->assertUnauthorized();
        $this->putJson('/api/programs/'.$program->id, [])->assertUnauthorized();
        $this->patchJson('/api/programs/'.$program->id, [])->assertUnauthorized();
        $this->deleteJson('/api/programs/'.$program->id)->assertUnauthorized();
    }

    public function test_all_program_endpoints_reject_invalid_jwt(): void
    {
        $program = Program::create([
            'nama' => 'Program Uji JWT',
            'deskripsi' => 'Digunakan untuk pengujian autentikasi.',
        ]);

        $this->withToken('invalid-token');

        $this->getJson('/api/programs')->assertUnauthorized();
        $this->postJson('/api/programs', [])->assertUnauthorized();
        $this->getJson('/api/programs/'.$program->id)->assertUnauthorized();
        $this->putJson('/api/programs/'.$program->id, [])->assertUnauthorized();
        $this->patchJson('/api/programs/'.$program->id, [])->assertUnauthorized();
        $this->deleteJson('/api/programs/'.$program->id)->assertUnauthorized();
    }

    public function test_expired_jwt_is_rejected(): void
    {
        $user = User::factory()->create();
        $now = time();

        $expiredToken = JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now - 7200,
            'exp' => $now - 3600,
        ], $this->jwtSecret(), 'HS256');

        $this->withToken($expiredToken)
            ->getJson('/api/programs')
            ->assertUnauthorized();
    }

    private function jwtForUser(User $user): string
    {
        $now = time();

        return JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $this->jwtSecret(), 'HS256');
    }

    private function jwtSecret(): string
    {
        $secret = env('JWT_SECRET');

        return is_string($secret) && $secret !== '' ? $secret : (string) config('app.key');
    }
}
