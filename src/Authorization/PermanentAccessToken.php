<?php declare(strict_types=1);

namespace ShiptheoryClient\Authorization;

class PermanentAccessToken implements AccessTokenInterface
{
    /**
     * Contructor
     *
     * @param string $token Permanent Shiptheory access token.
     */
    public function __construct(
        private string $token,
    ) {}

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
