# UTransfer — Financial Transfer API

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3"/>
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 11"/>
  <img src="https://img.shields.io/badge/Docker-ready-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker"/>
  <img src="https://img.shields.io/badge/Tests-PHPUnit%2011-green?style=flat-square&logo=phpunit" alt="PHPUnit 11"/>
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=flat-square" alt="MIT"/>
</p>

A RESTful API that simulates a simplified financial transfer system between users — think of it as a stripped-down PicPay. Users can register, authenticate and transfer funds to each other. All transfers are processed asynchronously through a dedicated Redis queue, and notifications go through a separate lower-priority queue.

---

## Architecture

The project follows **Clean Architecture** principles, keeping business rules completely isolated from framework and infrastructure details. The codebase is split into four explicit layers:

```
app/
├── Domain/               # Pure business rules — no framework dependencies
│   ├── Transaction/
│   │   ├── Entities/     # Transaction entity (readonly)
│   │   ├── ValueObjects/ # Money (immutable, arithmetic, format)
│   │   ├── Enums/        # TransactionType (PHP 8.1 backed enum)
│   │   ├── Exceptions/   # InsufficientFunds, ShopUserCannotTransfer, UnauthorizedTransaction
│   │   └── Contracts/    # TransactionRepositoryInterface
│   └── User/
│       ├── Enums/        # UserRole, DocumentType
│       └── Contracts/    # UserRepositoryInterface, WalletRepositoryInterface
│
├── Application/          # Use cases and DTOs — orchestrates domain rules
│   ├── Transaction/
│   │   ├── UseCases/     # TransferUseCase
│   │   ├── DTOs/         # TransferInputDTO (readonly class)
│   │   └── Contracts/    # AuthorizerServiceInterface
│   └── User/
│       ├── UseCases/     # RegisterUserUseCase, AuthenticateUserUseCase
│       └── DTOs/         # RegisterUserInputDTO (readonly class)
│
├── Infrastructure/       # Framework and external integrations
│   ├── Persistence/
│   │   ├── Models/       # Eloquent models (UserModel, TransactionModel, WalletModel)
│   │   └── Repositories/ # Eloquent implementations of domain contracts
│   ├── External/         # GuzzleAuthorizerService (external payment authorizer)
│   ├── Queue/Jobs/       # ProcessTransferJob (transactions queue)
│   └── Notifications/    # Email notifications (notifications queue)
│
└── Presentation/         # HTTP layer — controllers, requests, resources
    └── Http/
        ├── Controllers/  # AuthController, TransactionController
        ├── Requests/     # FormRequests with validation rules
        ├── Resources/    # UserResource, TransactionResource
        └── Traits/       # ApiResponseTrait (uniform JSON envelope)
```

### How a transfer flows

```
POST /api/transfer
      │
      ▼
TransferRequest (validate)
      │
      ▼
TransactionController
      │
      ▼
TransferUseCase ──── validates role ────► ShopUserCannotTransferException (403)
      │          └── validates balance ──► InsufficientFundsException (422)
      │
      ▼
ProcessTransferJob ──► [transactions queue]
      │
      ├── GuzzleAuthorizerService.authorize() ──► UnauthorizedTransactionException (403)
      ├── TransactionRepository.create()
      ├── WalletRepository.debit() + credit()  (inside DB transaction)
      └── Notifications ──► [notifications queue]
```

---

## Requirements

- [Docker](https://www.docker.com/) & Docker Compose

That's it — PHP, Composer and MySQL are all handled inside the containers.

---

## Quick Start

```bash
# 1. Clone and enter the project
git clone <repo-url> utransfer
cd utransfer

# 2. Copy environment file and set your values
cp .env.example .env

# 3. Build and start all services
docker compose up -d --build

# 4. Install PHP dependencies
docker compose exec api composer install

# 5. Generate app key
docker compose exec api php artisan key:generate

# 6. Run migrations and seed user types
docker compose exec api php artisan migrate --seed

# 7. The API is now available at http://localhost
```

---

## Environment Variables

| Variable | Default | Description |
|---|---|---|
| `APP_KEY` | _(generate)_ | Laravel application encryption key |
| `APP_DEBUG` | `true` | Show detailed errors (set `false` in production) |
| `DB_HOST` | `db` | MySQL container hostname |
| `DB_DATABASE` | `api` | Database name |
| `REDIS_HOST` | `redis` | Redis container hostname |
| `QUEUE_CONNECTION` | `redis` | Queue driver (`redis` or `sync` for local debug) |
| `MAIL_HOST` | `mailhog` | SMTP host (MailHog in dev, check `http://localhost:8025`) |
| `MAIL_PORT` | `1025` | SMTP port |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost` | Domains allowed for Sanctum stateful auth |

---

## API Reference

All responses share this envelope:

```json
{
  "success": true | false,
  "message": "Human-readable result",
  "data": { ... } | null,
  "meta": { "timestamp": "2024-01-01T12:00:00.000000Z" }
}
```

Validation errors also include an `errors` object with field-level messages.

---

### Auth

#### `POST /api/register`

Create a new user account. Either `cpf` (11 digits) or `cnpj` (14 digits) must be provided.

**Request**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "secret123",
  "cpf": "12345678901",
  "user_type_id": 1
}
```

**Response `201`**
```json
{
  "success": true,
  "message": "Account created successfully.",
  "data": {
    "token": "1|abc...",
    "user": { "id": 1, "name": "Jane Doe", "email": "jane@example.com" }
  },
  "meta": { "timestamp": "..." }
}
```

---

#### `POST /api/login`

Authenticate and receive a bearer token.

**Request**
```json
{
  "email": "jane@example.com",
  "password": "secret123"
}
```

**Response `200`**
```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": { "token": "1|abc...", "user": { ... } },
  "meta": { "timestamp": "..." }
}
```

---

#### `POST /api/logout` 🔒

Revoke the current access token.

**Response `204 No Content`**

---

### Transfers

#### `POST /api/transfer` 🔒

Initiate a transfer to another user. The operation is queued asynchronously — the sender receives a success/failure email once processed.

> **Note:** Only `standart` (regular) users can send transfers. `shop` accounts cannot initiate transfers.

**Request**
```json
{
  "user_id_to": 2,
  "amount": 150.00
}
```

**Response `202 Accepted`**
```json
{
  "success": true,
  "message": "Transfer queued for processing.",
  "data": null,
  "meta": { "timestamp": "..." }
}
```

| Status | Reason |
|---|---|
| `202` | Transfer accepted and queued |
| `401` | Missing or invalid bearer token |
| `403` | Shop accounts cannot send transfers |
| `422` | Insufficient balance or validation error |

🔒 = Requires `Authorization: Bearer <token>` header.

---

## Queue System

Transfers and notifications run on separate Redis queues to avoid one blocking the other:

| Queue | Worker container | Priority | `--tries` | Backoff |
|---|---|---|---|---|
| `transactions` | `queue-transactions` | High | 3 | 10s → 30s → 60s |
| `notifications` | `queue-notifications` | Low | 5 | 30s → 60s → 120s |

When a job exhausts all retries, the `failed()` method fires a failure notification email to the sender.

---

## Running Tests

```bash
# All tests
docker compose exec api php artisan test

# Specific suite
docker compose exec api php artisan test --testsuite=Unit
docker compose exec api php artisan test --testsuite=Feature

# With coverage (requires Xdebug or PCOV)
docker compose exec api vendor/bin/phpunit --coverage-text
```

### Test structure

```
tests/
├── Feature/
│   ├── AuthTest.php       — register, login, logout flows
│   └── TransferTest.php   — transfer validation, auth guard, queue dispatch
└── Unit/
    └── Domain/
        ├── MoneyTest.php          — Money value object (arithmetic, guards, format)
        └── TransferUseCaseTest.php — use case with mocked repositories
```

---

## Services

| Service | URL | Description |
|---|---|---|
| API | `http://localhost` | Main application (via Nginx) |
| MailHog | `http://localhost:8025` | Catch-all SMTP for local email testing |
| MySQL | `localhost:3306` | Database |
| Redis | `localhost:6379` | Cache + queue broker |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Runtime | PHP 8.3 |
| Framework | Laravel 11 |
| Auth | Laravel Sanctum |
| Queue | Redis + Laravel Queues |
| Database | MySQL 8.0 |
| Web server | Nginx 1.25 |
| Containerization | Docker + Docker Compose |
| Tests | PHPUnit 11 |
| Code style | Laravel Pint |
| Static analysis | Larastan |

---

## License

MIT
