<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Requests;

use ChrisJohnLeah\BigChange\Data\BigChangeCredentials;
use Saloon\Enums\Method;
use Saloon\Traits\RequestProperties\HasQuery;

final class GetRequest extends BigChangeRequest
{
    use HasQuery;

    protected Method $method = Method::GET;

    /** @var array<string, mixed> */
    private readonly array $parameters;

    public function __construct(
        string $path,
        array $parameters,
        BigChangeCredentials $credentials,
    ) {
        parent::__construct($credentials);
        $this->path = $path;
        $this->parameters = $parameters;
    }

    private readonly string $path;

    public function resolveEndpoint(): string
    {
        return ltrim($this->path, '/');
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter(
            $this->parameters,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );
    }
}
