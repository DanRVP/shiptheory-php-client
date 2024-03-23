<?php declare(strict_types=1);

namespace ShiptheoryClient\Authorization;

use Psr\Http\Client\ClientInterface;

abstract class AbstractAccessToken
{
    /**
     * @var \Psr\Http\Client\ClientInterface PSR-18 compliant HTTP client to make HTTP requests.
     */
    protected ClientInterface $http_client;

    /**
     * Returns an access token string to be used in API requests.
     */
    abstract public function getToken(): string;

    /**
     * Set the value of http_client
     *
     * @param \Psr\Http\Client\ClientInterface $http_client PSR-18 compliant HTTP client to make HTTP requests.
     */
    public function setHttpClient(ClientInterface $http_client): self
    {
        $this->http_client = $http_client;

        return $this;
    }
}

