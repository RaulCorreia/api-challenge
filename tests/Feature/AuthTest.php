<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();
    }

    /**
     *
     */
    public function testShouldCreateUser(): void
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
            'cpf' => '12345678912',
            'user_type_id' => $this->faker->numberBetween(1, 2)
        ];

        $this->postJson('api/register', $data)
            ->assertStatus(201)
            ->assertJsonStructure([[
                'message' => [
                    'token'
                ],
                'success'
            ]]);

        $users = User::where('email', $data['email'])->get();
        $user = $users->first();

        $this->assertCount(1, $users);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['cpf'], $user->document);
    }

    /**
     *
     */
    public function testShouldNotCreateUserByDuplicated(): void
    {
        $userAlreadyCreated = factory(User::class)->create();

        $data = [
            'name' => 'Foo bar',
            'email' => $userAlreadyCreated->email,
            'password' => $this->faker->password,
            'cpf' => $userAlreadyCreated->document,
            'user_type_id' => $this->faker->numberBetween(1, 2)
        ];

        $response = $this->postJson('api/register', $data)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);

        $response = json_decode($response->getContent());

        $this->assertEquals('The given data was invalid.', $response->message);
        $this->assertTrue(array_key_exists('email', $response->errors));
        $this->assertTrue(array_key_exists('cpf', $response->errors));
        $this->assertEquals('The email has already been taken.', $response->errors->email[0]);
        $this->assertEquals('The cpf has already been taken.', $response->errors->cpf[0]);
    }

    /**
     *
     */
    public function testShouldLogin(): void
    {
        $user = factory(User::class)->create();

        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson('api/login', $data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'message' => [
                    'token'
                ],
                'success'
            ]);

        $response = json_decode($response->getContent());
        $this->assertTrue($response->success);
    }

    public function testShouldNotLogin(): void
    {
        $user = factory(User::class)->create();

        $data = [
            'email' => $user->email,
            'password' => '123456',
        ];

        $response = $this->postJson('api/login', $data)
            ->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'success'
            ]);

        $response = json_decode($response->getContent());
        $this->assertEquals('Unauthorized', $response->message);
        $this->assertFalse($response->success);
    }
}
