# shiptheory-php-client
Client to handle sending HTTP requests to the Shiptheory V1 REST API. Handles logging, authentication/refreshing tokens. Uses PSR compliant interfaces and dependency injection where possible.

## Installation 
```
composer require dan-rogers/shiptheory-php-client
```

## Example Usage
```php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ShiptheoryClient\Authorization\CredentialsAccessToken;
use ShiptheoryClient\ShiptheoryClient;

$client = new Client();
$token =  new CredentialsAccessToken('dan.rogers@shiptheory.com', 'password');
$log = new Logger('name');
$log->pushHandler(new StreamHandler('path/to/your.log', Level::Debug));

$shiptheory = new ShiptheoryClient($client, $token, $log, 'partner_tag');

$res = $shiptheory->listTags();
var_dump($res->getBody()->getContents());
```
