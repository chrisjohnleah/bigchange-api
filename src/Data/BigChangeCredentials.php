<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Data;

use InvalidArgumentException;

final readonly class BigChangeCredentials
{
    public const API_BASE_URL = 'https://api.bigchange.com/v1';

    public const ASSET_MANAGEMENT_API_BASE_URL = 'https://api.bigchange.com/asset-management/v1';

    public const TOKEN_URL = 'https://api.bigchange.com/auth/tokens';

    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $customerId,
        public string $apiBaseUrl = self::API_BASE_URL,
        public string $tokenUrl = self::TOKEN_URL,
        public ?string $scope = null,
        public string $assetManagementApiBaseUrl = self::ASSET_MANAGEMENT_API_BASE_URL,
    ) {
        if ($this->clientId === '' || $this->clientSecret === '' || $this->customerId === '') {
            throw new InvalidArgumentException('BigChange client ID, client secret, and customer ID are required.');
        }

        if ($this->apiBaseUrl === '' || $this->tokenUrl === '' || $this->assetManagementApiBaseUrl === '') {
            throw new InvalidArgumentException('BigChange API, asset-management API, and token URLs are required.');
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            clientId: (string) ($values['client_id'] ?? $values['clientId'] ?? ''),
            clientSecret: (string) ($values['client_secret'] ?? $values['clientSecret'] ?? ''),
            customerId: (string) ($values['customer_id'] ?? $values['customerId'] ?? ''),
            apiBaseUrl: rtrim((string) ($values['api_base_url'] ?? $values['apiBaseUrl'] ?? self::API_BASE_URL), '/'),
            tokenUrl: (string) ($values['token_url'] ?? $values['tokenUrl'] ?? self::TOKEN_URL),
            scope: isset($values['scope']) && (string) $values['scope'] !== '' ? (string) $values['scope'] : null,
            assetManagementApiBaseUrl: rtrim((string) ($values['asset_management_api_base_url'] ?? $values['assetManagementApiBaseUrl'] ?? self::ASSET_MANAGEMENT_API_BASE_URL), '/'),
        );
    }

    public function tokenBaseUrl(): string
    {
        $parts = parse_url($this->tokenUrl);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('BigChange token URL must be an absolute URL.');
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $parts['scheme'].'://'.$parts['host'].$port;
    }

    public function tokenEndpoint(): string
    {
        $parts = parse_url($this->tokenUrl);
        $path = is_array($parts) ? (string) ($parts['path'] ?? '') : '';

        return ltrim($path, '/');
    }
}
