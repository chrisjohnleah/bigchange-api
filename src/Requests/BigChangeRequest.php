<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Requests;

use ChrisJohnLeah\BigChange\Data\BigChangeCredentials;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\RequestProperties\HasHeaders;

abstract class BigChangeRequest extends Request
{
    use AcceptsJson;
    use HasHeaders;

    public ?int $tries = 3;

    public ?int $retryInterval = 1;

    public ?bool $throwOnMaxTries = false;

    public ?bool $useExponentialBackoff = true;

    public function __construct(protected readonly BigChangeCredentials $credentials) {}

    protected function defaultHeaders(): array
    {
        return [
            'Customer-Id' => $this->credentials->customerId,
            'Content-Type' => 'application/json',
        ];
    }

    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        $status = $exception instanceof RequestException ? $exception->getResponse()?->status() : null;

        return $status === 429 || ($status !== null && $status >= 500);
    }
}
