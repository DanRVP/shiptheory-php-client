<?php declare(strict_types=1);

namespace ShiptheoryClient;

use Exception;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ShiptheoryClient\Authorization\AccessTokenInterface;

class ShiptheoryClient
{
    /**
     * @var string The base URL for the Shiptheory API.
     */
    private const BASE_URL = 'https://api.shiptheory.com/v1/';

    /**
     * @var array Standard headers used in all requests.
     */
    private const HEADERS = [
        'Accept' => 'application/json',
        'Content-Type' =>  'application/json',
    ];

    /**
     * Constructor
     *
     * @param ClientInterface $api_client PSR-18 compliant API client to make HTTP requests.
     * @param AccessTokenInterface $access_token Access token provider.
     * @param LoggerInterface|null $logger (Optional) PSR-3 compliant logger to write to logs.
     * @param string|null $partner_tag (Optional) Shiptheory partner tag. Not needed in most cases.
     */
    public function __construct(
        private ClientInterface $api_client,
        private AccessTokenInterface $access_token,
        private ?LoggerInterface $logger,
        private ?string $partner_tag,
    ) {
        if ($logger === null) {
            $this->logger = new NullLogger();
        }
    }

    /**
     * Make a request to the Shiptheory API.
     *
     * @param string $method HTTP method
     * @param string $uri The endpoint to query
     * @param string|null $data (conditional) Required for post and put requests.
     */
    public function makeRequest(string $method, string $uri, ?string $body = null): ResponseInterface
    {
        $request = new Request(
            $method,
            self::BASE_URL . $uri,
            array_merge(self::HEADERS, $this->access_token->getToken()),
            $body,
        );

        $this->logger->debug('Stringed request');

        try {
            $response = $this->api_client->sendRequest($request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $this->logger->debug('Stringed response');
        return $response;
    }

    /**
     * Book in a shipment with Shiptheory
     *
     * @param string $data json string of data.
     * @return Response|Error
     */
    public function bookShipment($data)
    {
        return $this->makeRequest('post', 'shipments', $data);
    }

    /**
     * View a shipment
     *
     * @param string $reference The unique reference used when creating the shipment.
     * @return Response|Error
     */
    public function viewShipment($reference)
    {
        return $this->makeRequest('get', 'shipments/' . $reference);
    }

    /**
     * Calls the shipment/list API endpoint and returns a result.
     *
     * @param string $query_params URL query params to filter by.
     * @return Response|Error
     */
    public function listShipment($query_params)
    {
        return $this->makeRequest('get', 'shipments/list' . $query_params);
    }

    /**
     * Calls the shipment/search API endpoint and returns a result.
     *
     * @param string $query_params URL query params to filter by.
     * @return Response|Error
     */
    public function searchShipment($query_params)
    {
        return $this->makeRequest('get', 'shipments/search' . $query_params);
    }

    /**
     * Create a new return label
     *
     * @param string $data json string of data.
     * @return Response|Error
     */
    public function createReturnLabel($data)
    {
        return $this->makeRequest('post', 'returns', $data);
    }

    /**
     * Get a list of outgoing delivery services.
     *
     * @return Response|Error
     */
    public function getOutgoingDeliveryServices()
    {
        return $this->makeRequest('get', 'services');
    }

    /**
     * Get a list of incoming delivery services.
     *
     * @return Response|Error
     */
    public function getIncomingDeliveryServices()
    {
        return $this->makeRequest('get', 'services/incoming');
    }

    /**
     * Get a list of package sizes.
     *
     * @return Response|Error
     */
    public function getPackageSizes($query_params)
    {
        return $this->makeRequest('get', 'packages/sizes' . $query_params);
    }

    /**
     * Add a new product.
     *
     * @param string $data json string of data.
     * @return Response|Error
     */
    public function addProduct($data)
    {
        return $this->makeRequest('post', 'products', $data);
    }

    /**
     * Update a product.
     *
     * @param string $data json string of data.
     * @return Response|Error
     */
    public function updateProduct($sku, $data)
    {
        return $this->makeRequest('put', 'products/update/' . $sku, $data);
    }

    /**
     * View a product from your product catalouge.
     *
     * @param string $sku The unique product SKU.
     * @return Response|Error
     */
    public function viewProduct($sku)
    {
        return $this->makeRequest('get', 'products/view/' . $sku);
    }

    /**
     * View a list of products from your product catalouge.
     *
     * @param string $sku The unique product SKU.
     * @return Response|Error
     */
    public function listProducts($query_params)
    {
        return $this->makeRequest('get', 'products' . $query_params);
    }
}
