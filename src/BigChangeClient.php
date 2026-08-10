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
use Saloon\Http\Faking\MockClient;

final class BigChangeClient extends Connector
{
    public const MAX_PAGE_SIZE = 1000;

    private readonly AccessTokenAuthenticator $accessTokenAuthenticator;

    public function __construct(
        public readonly BigChangeCredentials $credentials,
        string $accessToken,
    ) {
        $this->accessTokenAuthenticator = new AccessTokenAuthenticator($accessToken);
    }

    public static function fromClientCredentials(BigChangeCredentials $credentials, ?MockClient $mockClient = null): self
    {
        $tokenConnector = new BigChangeTokenConnector($credentials);

        if ($mockClient !== null) {
            $tokenConnector->withMockClient($mockClient);
        }

        $authenticator = $tokenConnector->getAccessToken();
        $client = new self($credentials, $authenticator->getAccessToken());

        return $mockClient === null ? $client : $client->withMockClient($mockClient);
    }

    public function resolveBaseUrl(): string
    {
        return $this->credentials->apiBaseUrl;
    }

    public function assetManagementClient(): BigChangeAssetClient
    {
        return new BigChangeAssetClient($this->credentials, $this->accessTokenAuthenticator->getAccessToken());
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
     * Read one page of a BigChange REST collection. The provider caps pages at
     * 1,000 records; the SDK enforces that boundary for every caller.
     *
     * @param  array<string, mixed>  $query
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
    public function contacts(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('contacts', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function contactGroups(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('contactGroups', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function departmentCodes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('departmentCodes', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function flags(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('flags', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function nominalCodes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('nominalCodes', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function vatCodes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('vatCodes', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function persons(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('persons', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function jobs(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function users(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('users', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function resources(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('resources', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function resourceGroups(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('resourceGroups', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function jobTypes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobTypes', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function jobGroups(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobGroups', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function jobRecurrences(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobRecurrences', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function notes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('notes', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function noteTypes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('noteTypes', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function stock(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('stock', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function vehicles(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('vehicles', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function worksheets(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('worksheets', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function worksheetGroups(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('worksheetGroups', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function resourceTimesheets(string|int $resourceId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('resources/'.$this->pathSegment($resourceId).'/timesheets', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function salesOpportunities(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('salesOpportunities', $query, $pageNumber, $pageSize);
    }

    public function jobLineItems(string|int $jobId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs/'.$this->pathSegment($jobId).'/lineItems', $query, $pageNumber, $pageSize);
    }

    public function jobStatusHistory(string|int $jobId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs/'.$this->pathSegment($jobId).'/statusHistory', $query, $pageNumber, $pageSize);
    }

    public function jobStock(string|int $jobId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs/'.$this->pathSegment($jobId).'/stock', $query, $pageNumber, $pageSize);
    }

    public function jobWorksheets(string|int $jobId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs/'.$this->pathSegment($jobId).'/worksheets', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function worksheetAnswers(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('worksheetAnswers', $query, $pageNumber, $pageSize);
    }

    public function jobConstraints(string|int $jobId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs/'.$this->pathSegment($jobId).'/constraints', $query, $pageNumber, $pageSize);
    }

    public function jobFlagHistory(string|int $jobId, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('jobs/'.$this->pathSegment($jobId).'/flags/history', $query, $pageNumber, $pageSize);
    }

    public function jobActiveFlag(string|int $jobId): BigChangeResponse
    {
        return $this->get('jobs/'.$this->pathSegment($jobId).'/flags');
    }

    /** @param array<string, mixed> $query */
    public function finance(string $resource, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('finance/'.trim($resource, '/'), $query, $pageNumber, $pageSize);
    }

    public function financeLineItems(string $resource, string|int $id, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->page('finance/'.trim($resource, '/').'/'.$this->pathSegment($id).'/lineItems', $query, $pageNumber, $pageSize);
    }

    public function purchaseOrders(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->finance('purchaseOrders', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function invoices(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->finance('invoices', $query, $pageNumber, $pageSize);
    }

    /** @param array<string, mixed> $query */
    public function quotes(array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->finance('quotes', $query, $pageNumber, $pageSize);
    }

    public function purchaseOrderLineItems(string|int $id, array $query = [], int $pageNumber = 1, int $pageSize = self::MAX_PAGE_SIZE): BigChangePage
    {
        return $this->financeLineItems('purchaseOrders', $id, $query, $pageNumber, $pageSize);
    }

    private function pathSegment(string|int $value): string
    {
        return rawurlencode((string) $value);
    }
}
