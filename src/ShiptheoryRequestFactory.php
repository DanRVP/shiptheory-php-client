<?php declare(strict_types=1);

namespace ShiptheoryClient;

use Nyholm\Psr7\Request;

final class ShiptheoryRequestFactory
{
    /**
     * @var string The base URL for the Shiptheory API.
     */
    private const BASE_URL = 'https://api.shiptheory.com/v1';

    /**
     * @var array Standard headers used in all requests.
     */
    private const HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' =>  'application/json',
    ];

    /**
     * @var string HTTP request method "GET"
     */
    public const HTTP_GET = 'GET';

    /**
     * @var string HTTP request method "POST"
     */
    public const HTTP_POST = 'POST';

    /**
     * @var string HTTP request method "PUT"
     */
    public const HTTP_PUT = 'PUT';

    /**
     * @var string HTTP request method "DELETE"
     */
    public const HTTP_DELETE = 'DELETE';

    /**
     * Create a request with the correct headers and URL for a request to the Shiptheory API.
     *
     * @param string $method HTTP method
     * @param string $uri The endpoint to query
     * @param string $access_token Access token to pass in the Authorization header
     * @param string|null $data (conditional) Required for post and put requests.
     */
    final public static function createRequest(
        string $method,
        string $path,
        string $access_token,
        ?string $body = null
    ) {
        return new Request(
            $method,
            self::BASE_URL . $path,
            array_merge(
                self::HEADERS,
                ['Authorization' => "Bearer $access_token"]
            ),
            $body,
        );
    }
}
