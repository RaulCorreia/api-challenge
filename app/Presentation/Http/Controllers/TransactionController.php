<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Transaction\DTOs\TransferInputDTO;
use App\Application\Transaction\UseCases\TransferUseCase;
use App\Domain\Transaction\ValueObjects\Money;
use App\Presentation\Http\Requests\TransferRequest;
use App\Presentation\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TransactionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly TransferUseCase $transferUseCase,
    ) {}

    /**
     * Initiate a transfer to another user.
     *
     * @response 202 { "success": true, "message": "Transfer queued for processing." }
     * @response 403 { "success": false, "message": "Shop accounts are not allowed to initiate transfers." }
     * @response 422 { "success": false, "message": "You don't have enough balance to complete this transfer." }
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $dto = new TransferInputDTO(
            senderId:    auth()->id(),
            recipientId: $request->user_id_to,
            amount:      new Money((float) $request->amount),
        );

        $this->transferUseCase->execute($dto);

        return $this->success(
            data:    null,
            message: 'Transfer queued for processing.',
            status:  202,
        );
    }
}

