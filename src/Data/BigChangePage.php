<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Data;

final readonly class BigChangePage
{
    /**
     * @param  array<int, mixed>  $items
     */
    public function __construct(
        public array $items,
        public int $pageNumber,
        public int $pageSize,
        public int $pageItemCount,
        public int $status = 200,
        public mixed $payload = null,
    ) {}

    public static function fromResponse(BigChangeResponse $response, int $pageNumber, int $pageSize): self
    {
        $items = $response->records();
        $count = self::integerFrom($response->data, ['pageItemCount', 'PageItemCount']) ?? count($items);

        return new self($items, $pageNumber, $pageSize, $count, $response->status, $response->data);
    }

    public function hasMore(): bool
    {
        return $this->items !== [] && $this->pageItemCount >= $this->pageSize;
    }

    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function nextPageNumber(): ?int
    {
        return $this->hasMore() ? $this->pageNumber + 1 : null;
    }

    public function json(): mixed
    {
        return $this->payload;
    }

    /**
     * @param  array<int, string>  $keys
     */
    private static function integerFrom(mixed $payload, array $keys): ?int
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return (int) $payload[$key];
            }
        }

        foreach (['data', 'Data', 'meta', 'Meta'] as $key) {
            if (isset($payload[$key])) {
                $nested = self::integerFrom($payload[$key], $keys);

                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }
}
