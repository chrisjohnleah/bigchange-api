<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange;

use ChrisJohnLeah\BigChange\Data\BigChangeJobWatchCredentials;
use ChrisJohnLeah\BigChange\Data\BigChangeResponse;
use ChrisJohnLeah\BigChange\Requests\AddJobAttachmentRequest;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;

final class BigChangeJobWatchClient extends Connector
{
    private readonly BasicAuthenticator $basicAuthenticator;

    public function __construct(public readonly BigChangeJobWatchCredentials $credentials)
    {
        $this->basicAuthenticator = new BasicAuthenticator($credentials->username, $credentials->password);
    }

    public static function fromCredentials(
        BigChangeJobWatchCredentials $credentials,
        ?MockClient $mockClient = null,
    ): self {
        $client = new self($credentials);

        return $mockClient === null ? $client : $client->withMockClient($mockClient);
    }

    public function resolveBaseUrl(): string
    {
        return $this->credentials->baseUrl;
    }

    protected function defaultAuth(): ?Authenticator
    {
        return $this->basicAuthenticator;
    }

    /**
     * Upload one reviewed document to a BigChange JobWatch job.
     *
     * @param  resource|StreamInterface|string  $file
     * @param  array{visibility_types?: string|null, add_to_job_group?: bool}  $options
     */
    public function addJobAttachment(
        string|int $jobId,
        mixed $file,
        string $filename,
        array $options = [],
    ): BigChangeResponse {
        return BigChangeResponse::fromSaloon($this->send(
            new AddJobAttachmentRequest($this->credentials, $jobId, $file, $filename, $options),
        ));
    }
}
