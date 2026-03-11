<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Models\UserModel;
use App\Infrastructure\Persistence\Models\WalletModel;
use Database\Seeders\UserTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use App\Infrastructure\Queue\Jobs\ProcessTransferJob;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use DatabaseTransactions;

    private UserModel  $standardUser;
    private WalletModel $standardWallet;
    private UserModel  $shopUser;
    private WalletModel $shopWallet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserTypeSeeder::class);

        $this->standardUser   = UserModel::factory()->standard()->create();
        $this->standardWallet = WalletModel::factory()->withBalance(100.00)->create([
            'user_id' => $this->standardUser->id,
        ]);

        $this->shopUser   = UserModel::factory()->shop()->create();
        $this->shopWallet = WalletModel::factory()->withBalance(50.00)->create([
            'user_id' => $this->shopUser->id,
        ]);
    }

    public function test_standard_user_can_initiate_a_transfer(): void
    {
        Queue::fake();

        $token = $this->getToken($this->standardUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfer', [
                'user_id_to' => $this->shopUser->id,
                'amount'     => 30.00,
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['success', 'message', 'meta'])
            ->assertJson(['success' => true]);

        Queue::assertPushedOn('transactions', ProcessTransferJob::class);
    }

    public function test_shop_user_cannot_initiate_a_transfer(): void
    {
        $token = $this->getToken($this->shopUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfer', [
                'user_id_to' => $this->standardUser->id,
                'amount'     => 10.00,
            ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_transfer_fails_when_balance_is_insufficient(): void
    {
        $token = $this->getToken($this->standardUser);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfer', [
                'user_id_to' => $this->shopUser->id,
                'amount'     => 9999.00,
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_transfer_requires_authentication(): void
    {
        $this->postJson('/api/transfer', [
            'user_id_to' => $this->shopUser->id,
            'amount'     => 10.00,
        ])->assertStatus(401);
    }

    public function test_transfer_validates_required_fields(): void
    {
        $token = $this->getToken($this->standardUser);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfer', [])
            ->assertStatus(422)
            ->assertJsonStructure(['success', 'errors']);
    }

    public function test_user_cannot_transfer_to_themselves(): void
    {
        $token = $this->getToken($this->standardUser);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/transfer', [
                'user_id_to' => $this->standardUser->id,
                'amount'     => 10.00,
            ])
            ->assertStatus(422);
    }

    private function getToken(UserModel $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }
}
