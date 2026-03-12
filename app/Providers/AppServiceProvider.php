<?php

namespace App\Providers;

use App\Application\Transaction\Contracts\AuthorizerServiceInterface;
use App\Domain\Transaction\Contracts\TransactionRepositoryInterface;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\User\Contracts\WalletRepositoryInterface;
use App\Infrastructure\External\GuzzleAuthorizerService;
use App\Infrastructure\Persistence\Repositories\EloquentTransactionRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Repositories\EloquentWalletRepository;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Guzzle HTTP client
        $this->app->singleton(Client::class, fn () => new Client([
            'timeout'         => 10,
            'connect_timeout' => 5,
        ]));

        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class,        EloquentUserRepository::class);
        $this->app->bind(WalletRepositoryInterface::class,      EloquentWalletRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);

        // External service bindings
        $this->app->bind(AuthorizerServiceInterface::class, GuzzleAuthorizerService::class);
    }

    public function boot(): void
    {
        //
    }
}
