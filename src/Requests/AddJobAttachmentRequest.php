<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Requests;

use ChrisJohnLeah\BigChange\Data\BigChangeJobWatchCredentials;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasMultipartBody;
use Saloon\Traits\RequestProperties\HasQuery;

final class AddJobAttachmentRequest extends BigChangeJobWatchRequest implements HasBody
{
    use HasMultipartBody;
    use HasQuery;

    protected Method $method = Method::POST;

    /**
     * @param  resource|StreamInterface|string  $file
     * @param  array{visibility_types?: string|null, add_to_job_group?: bool}  $options
     */
    public function __construct(
        BigChangeJobWatchCredentials $credentials,
        private readonly string|int $jobId,
        private readonly mixed $file,
        private readonly string $filename,
        private readonly array $options = [],
    ) {
        parent::__construct($credentials);
    }

    public function resolveEndpoint(): string
    {
        return 'services.ashx';
    }

    /** @return array<string, mixed> */
    protected function defaultQuery(): array
    {
        return array_filter([
            'action' => 'JobAddAttachments',
            'jobId' => (string) $this->jobId,
            'AddoJobGroup' => ($this->options['add_to_job_group'] ?? false) ? 'true' : null,
            'VisibilityTypes' => $this->options['visibility_types'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /** @return array<int, MultipartValue> */
    protected function defaultBody(): array
    {
        return [
            new MultipartValue(
                name: 'Files',
                value: $this->file,
                filename: $this->filename,
                headers: ['Content-Type' => 'application/pdf'],
            ),
        ];
    }
}
