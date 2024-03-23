<?php declare(strict_types=1);

namespace ShiptheoryClient\Authorization;

use DateTime;
use Exception;
use ShiptheoryClient\ShiptheoryRequestFactory;

class CredentialsAccessToken extends AbstractAccessToken
{
    /**
     * @var string The access token itself.
     */
    private string $token;

    /**
     * Contructor
     *
     * @param string $username Shiptheory username.
     * @param string $password Shiptheory passwrord.
     */
    public function __construct(
        private string $username,
        private string $password,
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
     *
     * @throws Exception
     */
    private function retrieveToken(): void
    {
        $request = ShiptheoryRequestFactory::createRequest(
            ShiptheoryRequestFactory::HTTP_GET,
            '/token',
            '',
            json_encode([
                'email' => $this->username,
                'password' => $this->password,
            ])
        );

        $response = $this->http_client->sendRequest($request);
        $body = json_decode(stream_get_contents($response->getBody()), true);
        if (is_null($body)) {
            throw new Exception('Unable to decode token response');
        }

        if ($response->getStatusCode() !== 200) {
            $error = 'Unable to authorise with Shiptheory';
            if (!empty($body['message'])) {
                $error .= ' The error from Shiptheory was: ' . $body['message'];
            }

            throw new Exception($error);
        }

        if (empty($body['data']['token'])) {
            throw new Exception('Shiptheory returned an OK response but the token is missing');
        }

        $this->token = $body['data']['token'];
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
        if (is_null($this->token)) {
            return false;
        }

        $token_data = $this->decodeToken($this->token);
        $expires = date_timestamp_set(new DateTime(), $token_data[1]['exp']);
        $expires->modify("-30 seconds");
        return $expires < new DateTime();
    }

    /**
     * Decode a JWT token and all its parts. Does not verify signature.
     *
     * @throws Exception
     */
    private function decodeToken(string $token): array
    {
        $parts = explode('.', $token);
        if (!$parts) {
            throw new Exception('Invalid JWT token');
        }

        $parts = array_map('base64_decode', $parts);
        if (count($parts) !== 3) {
            throw new Exception('Invalid JWT token');
        }

        $sig = array_pop($parts);
        $parts = array_map(fn($value) => json_decode($value, true), $parts);
        $parts[] = $sig;

        if (count($parts) !== 3) {
            throw new Exception('Unable to decode JWT token');
        }

        return $parts;
    }
}
