<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Data;

use Saloon\Http\Response;

final readonly class BigChangeResponse
{
    /**
     * @param  array<string, string|array<int, string>>  $headers
     */
    public function __construct(
        public int $status,
        public mixed $data,
        public array $headers = [],
    ) {}

    public static function fromSaloon(Response $response): self
    {
        /** @var array<string, string|array<int, string>> $headers */
        $headers = $response->headers()->all();

        try {
            $data = $response->json();
        } catch (\Throwable) {
            $data = $response->body();
        }

        return new self($response->status(), $data, $headers);
    }

    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        if (! is_array($this->data)) {
            return $default;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function body(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        }

        return (string) json_encode($this->data);
    }

    public function header(string $name): string|array|null
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Normalize the paged REST response while retaining the original payload.
     *
     * @return array<int, mixed>
     */
    public function records(): array
    {
        return self::recordsFrom($this->data);
    }

    /**
     * @return array<int, mixed>
     */
    private static function recordsFrom(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['items', 'data', 'Items', 'results', 'records'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return self::recordsFrom($payload[$key]);
            }
        }

        return [$payload];
    }
}
