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
     * @var string|null Shiptheory Partner Tag.
     */
    protected ?string $partner_tag;

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

    /**
     * Set the value of partner_tag
     *
     * @param string|null Shiptheory partner tag.
     */
    public function setPartnerTag(?string $partner_tag): self
    {
        $this->partner_tag = $partner_tag;

        return $this;
    }
}

