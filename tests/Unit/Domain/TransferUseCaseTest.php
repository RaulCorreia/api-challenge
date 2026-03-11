<?php

namespace Tests\Unit\Domain;

use App\Application\Transaction\Contracts\AuthorizerServiceInterface;
use App\Application\Transaction\DTOs\TransferInputDTO;
use App\Application\Transaction\UseCases\TransferUseCase;
use App\Domain\Transaction\Contracts\TransactionRepositoryInterface;
use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Exceptions\InsufficientFundsException;
use App\Domain\Transaction\Exceptions\ShopUserCannotTransferException;
use App\Domain\Transaction\ValueObjects\Money;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\Contracts\WalletRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class TransferUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private MockObject $userRepository;
    private MockObject $walletRepository;
    private MockObject $transactionRepository;
    private MockObject $authorizer;
    private TransferUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository        = $this->createMock(UserRepositoryInterface::class);
        $this->walletRepository      = $this->createMock(WalletRepositoryInterface::class);
        $this->transactionRepository = $this->createMock(TransactionRepositoryInterface::class);
        $this->authorizer            = $this->createMock(AuthorizerServiceInterface::class);

        $this->useCase = new TransferUseCase(
            $this->userRepository,
            $this->walletRepository,
            $this->transactionRepository,
            $this->authorizer,
        );
    }

    public function test_throws_when_sender_is_shop_user(): void
    {
        $this->expectException(ShopUserCannotTransferException::class);

        $shopUser = $this->buildUser(roleType: 'shop');

        $this->userRepository->method('findById')->willReturn($shopUser);

        $this->useCase->execute(new TransferInputDTO(
            senderId:    1,
            recipientId: 2,
            amount:      new Money(10.0),
        ));
    }

    public function test_throws_when_balance_is_insufficient(): void
    {
        $this->expectException(InsufficientFundsException::class);

        $standardUser = $this->buildUser(roleType: 'standart');
        $wallet       = $this->buildWallet(total: 5.0);

        $this->userRepository->method('findById')->willReturn($standardUser);
        $this->walletRepository->method('findByUserId')->willReturn($wallet);

        $this->useCase->execute(new TransferInputDTO(
            senderId:    1,
            recipientId: 2,
            amount:      new Money(100.0),
        ));
    }

    public function test_dispatches_job_when_transfer_is_valid(): void
    {
        Queue::fake();

        $standardUser = $this->buildUser(roleType: 'standart');
        $wallet       = $this->buildWallet(total: 200.0);

        $this->userRepository->method('findById')->willReturn($standardUser);
        $this->walletRepository->method('findByUserId')->willReturn($wallet);

        $dto = new TransferInputDTO(
            senderId:    1,
            recipientId: 2,
            amount:      new Money(50.0),
        );

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(TransactionEntity::class, $result);
        $this->assertSame(50.0, $result->amount->amount);
        $this->assertSame(TransactionType::TRANSFER, $result->type);

        Queue::assertPushedOn('transactions', \App\Infrastructure\Queue\Jobs\ProcessTransferJob::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function buildUser(string $roleType): object
    {
        return new class($roleType) {
            public object $userType;
            public function __construct(string $name)
            {
                $this->userType = new class($name) {
                    public function __construct(public string $name) {}
                };
            }
        };
    }

    private function buildWallet(float $total): object
    {
        return new class($total) {
            public function __construct(public float $total) {}
        };
    }
}

