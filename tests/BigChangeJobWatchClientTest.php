<?php

use ChrisJohnLeah\BigChange\BigChangeJobWatchClient;
use ChrisJohnLeah\BigChange\Data\BigChangeJobWatchCredentials;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function packageBigChangeJobWatchCredentials(): BigChangeJobWatchCredentials
{
    return new BigChangeJobWatchCredentials(
        username: 'api-user',
        password: 'api-password',
        companyKey: 'company-key',
        baseUrl: 'https://bigchange-jobwatch.test/v01',
    );
}

it('builds the documented JobWatch attachment request with basic auth and company key', function () {
    $mock = new MockClient([
        MockResponse::make(['Code' => 0, 'Result' => 'Uploaded'], 200),
    ]);
    $file = fopen('php://temp', 'r+');
    fwrite($file, '%PDF-reviewed');
    rewind($file);

    $response = BigChangeJobWatchClient::fromCredentials(packageBigChangeJobWatchCredentials(), $mock)
        ->addJobAttachment('job-42', $file, 'reviewed-form.pdf', [
            'visibility_types' => 'WebUser,Resource',
        ]);

    fclose($file);

    $request = $mock->getLastPendingRequest();

    expect($response->successful())->toBeTrue()
        ->and((string) $request?->getUri())
        ->toBe('https://bigchange-jobwatch.test/v01/services.ashx?action=JobAddAttachments&jobId=job-42&VisibilityTypes=WebUser%2CResource')
        ->and($request?->headers()->get('key'))->toBe('company-key')
        ->and($request?->headers()->get('Authorization'))->toStartWith('Basic ')
        ->and($request?->getRequest()->body()->get('Files')->filename)->toBe('reviewed-form.pdf');
});
