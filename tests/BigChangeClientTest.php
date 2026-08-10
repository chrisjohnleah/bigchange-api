<?php

use ChrisJohnLeah\BigChange\BigChangeClient;
use ChrisJohnLeah\BigChange\Data\BigChangeCredentials;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function packageBigChangeCredentials(): BigChangeCredentials
{
    return new BigChangeCredentials('client', 'secret', '14532', 'https://api.bigchange.test/v1', 'https://api.bigchange.test/auth/tokens');
}

it('enforces the documented BigChange page cap and customer header', function () {
    $mock = new MockClient([
        MockResponse::make(['items' => [['id' => 42]], 'pageItemCount' => 1]),
    ]);

    $page = (new BigChangeClient(packageBigChangeCredentials(), 'token'))
        ->withMockClient($mock)
        ->jobs(pageNumber: 2, pageSize: 5000);

    expect($page->pageSize)->toBe(1000)
        ->and($mock->getLastPendingRequest()?->getUri())
        ->toEqual('https://api.bigchange.test/v1/jobs?pageNumber=2&pageSize=1000')
        ->and($mock->getLastPendingRequest()?->headers()->get('Customer-Id'))->toBe('14532');
});

it('builds nested job resource paths', function () {
    $mock = new MockClient([MockResponse::make(['items' => []])]);

    (new BigChangeClient(packageBigChangeCredentials(), 'token'))
        ->withMockClient($mock)
        ->jobLineItems(42);

    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/v1/jobs/42/lineItems?pageNumber=1&pageSize=1000');
});

it('covers the documented contact and reference-data collections', function () {
    $mock = new MockClient([
        MockResponse::make(['items' => []]),
        MockResponse::make(['items' => []]),
        MockResponse::make(['items' => []]),
        MockResponse::make(['items' => []]),
        MockResponse::make(['items' => []]),
    ]);
    $client = (new BigChangeClient(packageBigChangeCredentials(), 'token'))->withMockClient($mock);

    $client->contactGroups(pageNumber: 2);
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/v1/contactGroups?pageNumber=2&pageSize=1000');

    $client->departmentCodes(pageSize: 250);
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/v1/departmentCodes?pageNumber=1&pageSize=250');

    $client->flags(['entityType' => 'job']);
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/v1/flags?entityType=job&pageNumber=1&pageSize=1000');

    $client->nominalCodes(pageNumber: 3);
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/v1/nominalCodes?pageNumber=3&pageSize=1000');

    $client->vatCodes(pageSize: 100);
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/v1/vatCodes?pageNumber=1&pageSize=100');
});

it('uses the separate asset-management base URL and its documented page cap', function () {
    $credentials = new BigChangeCredentials(
        clientId: 'client',
        clientSecret: 'secret',
        customerId: '14532',
        apiBaseUrl: 'https://api.bigchange.test/v1',
        tokenUrl: 'https://api.bigchange.test/auth/tokens',
        assetManagementApiBaseUrl: 'https://api.bigchange.test/asset-management/v1',
    );
    $mock = new MockClient([
        MockResponse::make(['items' => [['id' => 'asset-1']], 'pageItemCount' => 1]),
    ]);

    $client = (new BigChangeClient($credentials, 'token'))
        ->assetManagementClient()
        ->withMockClient($mock);
    $page = $client->assets(pageNumber: 3, pageSize: 5000);

    expect($page->pageSize)->toBe(2000)
        ->and((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/asset-management/v1/assets?pageNumber=3&pageSize=2000')
        ->and($mock->getLastPendingRequest()?->headers()->get('Customer-Id'))->toBe('14532')
        ->and($mock->getLastPendingRequest()?->headers()->get('Authorization'))->toBe('Bearer token');
});

it('builds documented asset child collection paths', function () {
    $credentials = new BigChangeCredentials('client', 'secret', '14532', assetManagementApiBaseUrl: 'https://api.bigchange.test/asset-management/v1');
    $mock = new MockClient([
        MockResponse::make(['items' => []]),
        MockResponse::make(['items' => []]),
    ]);
    $client = (new BigChangeClient($credentials, 'token'))->assetManagementClient()->withMockClient($mock);

    $client->assetImages('asset/42');
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/asset-management/v1/assets/asset%2F42/images?pageNumber=1&pageSize=2000');

    $client->assetServiceSchedules(42, ['activeOnly' => true]);
    expect((string) $mock->getLastPendingRequest()?->getUri())
        ->toBe('https://api.bigchange.test/asset-management/v1/assets/42/service-schedules?activeOnly=1&pageNumber=1&pageSize=2000');
});
