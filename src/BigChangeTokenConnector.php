<?php

declare(strict_types=1);

namespace ChrisJohnLeah\BigChange;

use ChrisJohnLeah\BigChange\Data\BigChangeCredentials;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;

final class BigChangeTokenConnector extends Connector
{
    use ClientCredentialsGrant;

    public function __construct(public readonly BigChangeCredentials $credentials) {}

    public function resolveBaseUrl(): string
    {
        return $this->credentials->tokenBaseUrl();
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $config = OAuthConfig::make()
            ->setClientId($this->credentials->clientId)
            ->setClientSecret($this->credentials->clientSecret)
            ->setTokenEndpoint($this->credentials->tokenEndpoint());

        if ($this->credentials->scope !== null) {
            $config->setDefaultScopes([$this->credentials->scope]);
        }

        return $config;
    }
}
