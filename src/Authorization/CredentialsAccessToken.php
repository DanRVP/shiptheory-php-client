<?php declare(strict_types=1);

namespace ShiptheoryClient\Authorization;

use DateTime;
use Psr\Http\Client\ClientInterface;

class CredentialsAccessToken implements AccessTokenInterface
{
    /**
     * @var string The access token itself.
     */
    private string $token;

    /**
     * @var DateTime Datetime stamp of when the token was generated.
     */
    private ?DateTime $token_age;

    /**
     * Contructor
     *
     * @param string $username Shiptheory username.
     * @param string $password Shiptheory passwrord.
     */
    public function __construct(
        private string $username,
        private string $password,
        private ClientInterface $api_client,
    ) {}

    /**
     * Get the value of token
     *
     * @return string
     */
    public function getToken(): string
    {
        $this->validateToken();
        return $this->token;
    }

    /**
     * Get a new access token and save it into memory.
     */
    private function retrieveToken(): void
    {
        $this->token = '';
    }

    /**
     * Checks to see if a token exists or has expired. If it has, then fetch a new one.
     */
    private function validateToken(): void
    {
        if (empty($this->token) || $this->checkTokenLifeExpired()) {
            $this->retrieveToken();
        }
    }

    /**
     * Check to see if a token has expired beyond its 60 min lifetime.
     *
     * @return bool
     */
    private function checkTokenLifeExpired(): bool
    {
        if (is_null($this->token_age)) {
            return false;
        }

        $diff = $this->token_age->diff(new DateTime());
        $minutes = 0;
        $minutes += $diff->d * 1440;
        $minutes += $diff->h * 60;
        $minutes += $diff->i;

        // Expire tokens 2 mins before expiry in case speeds are slow
        return $minutes > 58;
    }
}
