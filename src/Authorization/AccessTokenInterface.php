<?php declare(strict_types=1);

namespace ShiptheoryClient\Authorization;

interface AccessTokenInterface
{
    /**
     * Returns an access token string to be used in API requests.
     */
    public function getToken(): string;
}

