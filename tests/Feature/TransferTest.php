<?php

namespace Tests\Feature;

use App\Models\UserType;
use App\Models\Wallet;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    private $userStandart;
    private $walletStandart;
    private $userShop;
    private $walletShop;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpFaker();

        $userTypes = UserType::get();
        $this->userStandart = factory(User::class)->create([
            'user_type_id' => $userTypes->where('name', UserType::STANDART_USER)->first()->id
        ]);
        $this->walletStandart = factory(Wallet::class)->create([
            'user_id' => $this->userStandart->id,
            'total' => 15.00
        ]);

        $this->userShop = factory(User::class)->create([
            'user_type_id' => $userTypes->where('name', UserType::SHOP_USER)->first()->id
        ]);
        $this->walletShop = factory(Wallet::class)->create([
            'user_id' => $this->userShop->id,
            'total' => 10.00
        ]);

    }

    /**
     *
     */
    public function testShouldTransfer(): void
    {
        $token = $this->getToken($this->userStandart);
        $data = [
            'user_id_to' => $this->userShop->id,
            'amount' => 5.00,
        ];

        $response = $this->postJson('api/transfer', $data, ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonStructure([[
                'message',
                'success'
            ]])->decodeResponseJson();


        $this->walletStandart->refresh();
        $this->walletShop->refresh();

        $this->assertEquals('Transfer in progress', $response[0]['message']);
        $this->assertTrue($response[0]['success']);

        $this->assertEquals(10.00, $this->walletStandart->total);
        $this->assertEquals(15.00, $this->walletShop->total);
    }

    /**
     *
     */
    public function testShouldNotTransferByShop(): void
    {
        $token = $this->getToken($this->userShop);
        $data = [
            'user_id_to' => $this->userStandart->id,
            'amount' => 5.00,
        ];

        $response = $this->postJson('api/transfer', $data, ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonStructure([[
                'message',
                'success'
            ]])
            ->decodeResponseJson();

        $this->walletStandart->refresh();
        $this->walletShop->refresh();

        $this->assertEquals('Unable to perform this transaction', $response[0]['message']);
        $this->assertFalse($response[0]['success']);

        $this->assertEquals(15.00, $this->walletStandart->total);
        $this->assertEquals(10.00, $this->walletShop->total);
    }

    /**
     *
     */
    public function testShouldNotTransferByBalance(): void
    {
        $token = $this->getToken($this->userShop);
        $data = [
            'user_id_to' => $this->userStandart->id,
            'amount' => 25.00,
        ];

        $response = $this->postJson('api/transfer', $data, ['Authorization' => "Bearer {$token}"])
            ->assertStatus(200)
            ->assertJsonStructure([[
                'message',
                'success'
            ]])
            ->decodeResponseJson();

        $this->walletStandart->refresh();
        $this->walletShop->refresh();

        $this->assertEquals("You don't have enough balance", $response[0]['message']);
        $this->assertFalse($response[0]['success']);

        $this->assertEquals(15.00, $this->walletStandart->total);
        $this->assertEquals(10.00, $this->walletShop->total);
    }

    private function getToken($user): string
    {
        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson('api/login', $data);
        $response = json_decode($response->getContent());

        return $response->message->token;
    }
}
