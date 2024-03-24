<?php declare(strict_types=1);

namespace ShiptheoryClient;

use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ShiptheoryClient\Authorization\AbstractAccessToken;
use ShiptheoryClient\Authorization\AccessTokenInterface;

class ShiptheoryClient
{
    /**
     * Constructor
     *
     * @param ClientInterface $http_client PSR-18 compliant HTTP client to make HTTP requests.
     * @param AccessTokenInterface $access_token Access token provider.
     * @param LoggerInterface|null $logger (Optional) PSR-3 compliant logger to write to logs.
     * @param string|null $partner_tag (Optional) Shiptheory partner tag. Not needed in most cases.
     */
    public function __construct(
        private ClientInterface $http_client,
        private AbstractAccessToken $access_token,
        private ?LoggerInterface $logger = null,
        private ?string $partner_tag = null,
    ) {
        if (is_null($logger)) {
            $this->logger = new NullLogger();
        }

        $access_token->setHttpClient($this->http_client);
    }

    /**
     * Make a request to the Shiptheory API.
     *
     * @param string $method HTTP method
     * @param string $uri The endpoint to query
     * @param string|null $data (conditional) Required for post and put requests.
     * @throws Exception
     */
    public function makeRequest(string $method, string $uri, ?string $body = null): ResponseInterface
    {
        $transaction_id = md5($method . $uri . time());
        $request = ShiptheoryRequestFactory::createRequest($method, $uri, $this->access_token->getToken(), $body);
        $this->logger->debug($transaction_id . "\r\n" . HttpMessageTranslator::toHttpMessageString($request));

        try {
            $response = $this->http_client->sendRequest($request);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }

        $this->logger->debug($transaction_id . "\r\n" . HttpMessageTranslator::toHttpMessageString($response));
        return $response;
    }

    /**
     * Book in a shipment with Shiptheory
     *
     * @param string $data json string of data.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#book
     */
    public function bookShipment(string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_POST, '/shipments', $data);
    }

    /**
     * View a shipment
     *
     * @param string $reference The unique reference used when creating the shipment.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#view-shipment
     */
    public function viewShipment(string $reference): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/shipments/' . $reference);
    }

    /**
     * Calls the shipment/list API endpoint and returns a result.
     *
     * @param string $query_params URL query params to filter by.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#list-shipments
     */
    public function listShipment(string $query_params = ''): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/shipments/list' . $query_params);
    }

    /**
     * Calls the shipment/search API endpoint and returns a result.
     *
     * @param string $query_params URL query params to filter by.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#search-shipments
     */
    public function searchShipments(string $query_params = ''): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/shipments/search' . $query_params);
    }

    /**
     * Create a new return label
     *
     * @param string $data json string of data.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#creating-a-return-label
     */
    public function createReturnLabel(string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_POST, '/returns', $data);
    }

    /**
     * Get a list of outgoing delivery services.
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#outgoing-services
     */
    public function getOutgoingDeliveryServices(): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/services');
    }

    /**
     * Get a list of incoming delivery services.
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#incoming-services-returns-
     */
    public function getIncomingDeliveryServices(): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/services/incoming');
    }

    /**
     * Get a list of package sizes.
     *
     * @param string $query params Query params to include in the URL
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#packages
     */
    public function getPackageSizes(string $query_params = ''): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/packages/sizes' . $query_params);
    }

    /**
     * Add a new product.
     *
     * @param string $data json string of data.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#add-product
     */
    public function addProduct(string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_POST, '/products', $data);
    }

    /**
     * Update a product.
     *
     * @param string $sku SKU of the product to update.
     * @param string $data json string of data.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#update-product
     */
    public function updateProduct(string $sku, string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_PUT, '/products/update/' . $sku, $data);
    }

    /**
     * View a product from your product catalouge.
     *
     * @param string $sku The unique product SKU.
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#view-product
     */
    public function viewProduct(string $sku): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/products/view/' . $sku);
    }

    /**
     * View a list of products from your product catalouge.
     *
     * @param string $query_params Query params to include in the URL
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#list-products
     */
    public function listProducts(string $query_params = ''): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/products' . $query_params);
    }

    /**
     * View a list of products from your product catalouge.
     *
     * @param string $data JSON array of shipment references
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#picking-lists
     */
    public function downloadPickingList(string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_POST, '/picking_lists/download', $data);
    }

    /**
     * Add a shipment tag to Shiptheory.
     *
     * @param string $data JSON array of tag data
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#add-tags
     */
    public function addTag(string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_POST, '/tags', $data);
    }

    /**
     * View a tag.
     *
     * @param string $tag_id ID of the tag to get
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#view-tag
     */
    public function viewTag(string|int $tag_id): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/tags/' . (string) $tag_id);
    }

    /**
     * View a list of tags.
     *
     * @param string $query_params Query params to include in the URL
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#list-tags
     */
    public function listTags(string $query_params = ''): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_GET, '/tags' . $query_params);
    }

    /**
     * View a list of tags.
     *
     * @param string $query_params Query params to include in the URL
     * @param string $data Data to update tag with
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#update-tag
     */
    public function updateTag(string|int $tag_id, string $data): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_PUT, '/tags/' . (string) $tag_id, $data);
    }

    /**
     * View a list of tags.
     *
     * @param string $query_params Query params to include in the URL
     * @return \Psr\Http\Message\ResponseInterface
     * @link https://shiptheory.com/developer/index.html#update-tag
     */
    public function deleteTag(string|int $tag_id): ResponseInterface
    {
        return $this->makeRequest(ShiptheoryRequestFactory::HTTP_DELETE, '/tags/' . (string) $tag_id);
    }
}
