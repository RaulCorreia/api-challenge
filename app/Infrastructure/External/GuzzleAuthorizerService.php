<?php

namespace App\Infrastructure\External;

use App\Application\Transaction\Contracts\AuthorizerServiceInterface;
use App\Domain\Transaction\Exceptions\UnauthorizedTransactionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GuzzleAuthorizerService implements AuthorizerServiceInterface
{
    private const AUTHORIZER_URL = 'https://util.devi.tools/api/v2/authorize';

    public function __construct(
        private readonly Client $client
    ) {}

    /**
     * @throws UnauthorizedTransactionException
     */
    public function authorize(): void
    {
        try {
            $response = $this->client->get(self::AUTHORIZER_URL);
            $body     = json_decode($response->getBody()->getContents(), true);

            if (($body['data']['authorization'] ?? false) !== true) {
                throw new UnauthorizedTransactionException();
            }
        } catch (GuzzleException $e) {
            throw new UnauthorizedTransactionException(
                'Could not reach the payment authorizer. Please try again.'
            );
        }
    }
}

