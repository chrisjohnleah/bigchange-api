<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange\Data;

use InvalidArgumentException;

final readonly class BigChangeJobWatchCredentials
{
    public const BASE_URL = 'https://webservice.bigchange.com/v01';

    public function __construct(
        public string $username,
        public string $password,
        public string $companyKey,
        public string $baseUrl = self::BASE_URL,
    ) {
        if ($this->username === '' || $this->password === '' || $this->companyKey === '') {
            throw new InvalidArgumentException('BigChange JobWatch username, password, and company key are required.');
        }

        if ($this->baseUrl === '') {
            throw new InvalidArgumentException('BigChange JobWatch base URL is required.');
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            username: (string) ($values['jobwatch_username'] ?? $values['username'] ?? ''),
            password: (string) ($values['jobwatch_password'] ?? $values['password'] ?? ''),
            companyKey: (string) ($values['jobwatch_company_key'] ?? $values['company_key'] ?? ''),
            baseUrl: rtrim((string) ($values['jobwatch_base_url'] ?? $values['base_url'] ?? self::BASE_URL), '/'),
        );
    }
}
