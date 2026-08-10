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
