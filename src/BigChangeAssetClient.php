<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange;

use ChrisJohnLeah\BigChange\Data\BigChangeCredentials;
use ChrisJohnLeah\BigChange\Data\BigChangePage;
use ChrisJohnLeah\BigChange\Data\BigChangeResponse;
use ChrisJohnLeah\BigChange\Requests\GetRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\Connector;

/**
 * Client for BigChange's separate Asset Management API base URL.
 *
 * Asset-management responses are deliberately kept separate from the core
 * REST client because BigChange publishes a different base path and page cap.
 */
final class BigChangeAssetClient extends Connector
{
    public const MAX_PAGE_SIZE = 2000;

    private readonly AccessTokenAuthenticator $accessTokenAuthenticator;

    public function __construct(
        public readonly BigChangeCredentials $credentials,
        string $accessToken,
    ) {
        $this->accessTokenAuthenticator = new AccessTokenAuthenticator($accessToken);
    }

    public function resolveBaseUrl(): string
    {
        return $this->credentials->assetManagementApiBaseUrl;
    }

    protected function defaultAuth(): ?Authenticator
    {
        return $this->accessTokenAuthenticator;
    }

    /** @param array<string, mixed> $query */
    public function get(string $path, array $query = []): BigChangeResponse
    {
        return BigChangeResponse::fromSaloon($this->send(new GetRequest($path, $query, $this->credentials)));
    }

    /**
     * Read one page from an asset-management collection. The provider caps
     * this API at 2,000 records per page.
     *
     * @param array<string, mixed> $query
     */
    public function page(string $path, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        $pageNumber = max(1, $pageNumber);
        $pageSize = min(self::MAX_PAGE_SIZE, max(1, $pageSize));
        $query['pageNumber'] = $pageNumber;
        $query['pageSize'] = $pageSize;

        return BigChangePage::fromResponse($this->get($path, $query), $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function assets(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('assets', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function assetImages(string|int $assetId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('assets/'.$this->pathSegment($assetId).'/images', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function assetServiceSchedules(string|int $assetId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('assets/'.$this->pathSegment($assetId).'/service-schedules', $query, $pageNumber, $pageSize);
    }

    private function pathSegment(string|int $value): string
    {
        return rawurlencode((string) $value);
    }
}
