<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Models\UserModel;
use App\Infrastructure\Persistence\Models\UserTypeModel;
use Database\Seeders\UserTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserTypeSeeder::class);
    }

    public function test_user_can_register_with_cpf(): void
    {
        $data = [
            'name'         => 'John Doe Test',
            'email'        => 'john.doe.test@example.com',
            'password'     => 'secret123',
            'cpf'          => '12345678901',
            'user_type_id' => UserTypeModel::where('name', 'standart')->value('id'),
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
                'meta' => ['timestamp'],
            ]);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
        $this->assertDatabaseHas('wallets', ['user_id' => $response->json('data.user.id')]);
    }

    public function test_registration_fails_with_duplicate_email_or_document(): void
    {
        $existing = UserModel::factory()->create();

        $data = [
            'name'         => 'Duplicate User',
            'email'        => $existing->email,
            'password'     => 'secret123',
            'cpf'          => $existing->document,
            'user_type_id' => $existing->user_type_id,
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(422)
            ->assertJsonStructure(['success', 'message', 'errors'])
            ->assertJson(['success' => false]);

        $this->assertArrayHasKey('email',    $response->json('errors'));
        $this->assertArrayHasKey('cpf',      $response->json('errors'));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = UserModel::factory()->create(['password' => 'password']);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user'],
                'meta' => ['timestamp'],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = UserModel::factory()->create();

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user  = UserModel::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertStatus(204);
    }
}
