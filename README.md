# shiptheory-php-client
Client to handle sending HTTP requests to the Shiptheory V1 REST API. Handles logging, authentication/refreshing tokens. Uses PSR compliant interfaces and dependency injection where possible.

## Installation
```
composer require dan-rogers/shiptheory-php-client
```

## Usage
The client uses DI in conjunction with existing PSR standards so that you can operate in your preferred environment with your preferred libraries. The HTTP client required on instantiation must implement PSR18 and the optional logger you can use on instantiation must implement PSR3. 

### Tokens
#### Types of Token
Shiptheory offers 2 kinds of authentication:
1. **Credential based access:** Users must pass their login credentials to the `/token` endpoint to exchange them for a JWT access token which can then be used in subsequent requests. This token lasts for one hour, but a new one can be generated at any time. 
2. **Permanent tokens:** You can generate an unchanging, permanent token by logging into your account and going to https://helm.shiptheory.com/user_tokens

#### Implementation
To use credential access tokens you should pass an instance of `ShiptheoryClient\Authorization\CredentialsAccessToken` to your new instance of the Shiptheory Client. The token will automatically refresh itself. When instantiated you must pass your username and password as arguments.
```php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ShiptheoryClient\Authorization\CredentialsAccessToken;

$token = new CredentialsAccessToken('dan.rogers@shiptheory.com', 'password');
```

To use a permanent access token you should pass and instance of `ShiptheoryClient\Authorization\PermanentAccessToken` to your new instance of the Shiptheory Client. When instantiated you must pass your permanent token as the argument. 
```php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use ShiptheoryClient\Authorization\PermanentAccessToken;

$token = new PermanentAccessToken('ABC12345678');
```

### Example Usage 
The below example uses `guzzlehttp/guzzle` for the client, the `CredentialsAccessToken` for authorisation and `monolog/monolog` for the logging.
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

$log = new Logger('Shiptheory');
$handler = new StreamHandler('/path/to/your/logfile.log', Level::Debug);
$handler->setFormatter(new LineFormatter(null, null, true, true));
$log->pushHandler($handler);

$shiptheory = new ShiptheoryClient($client, $token, $log, 'partner_tag');

$res = $shiptheory->listTags();
var_dump($res->getBody()->getContents());
```

## Logging
### Enabling Logging
Logging is provided via DI. Pass in any PSR3 compliant logger object when instantiating ShiptheoryClient and the logging will be handled automatically for both requests and responses. If a logger is not provided then all logs a "blackholed" via use of `Psr\Log\NullLogger`.

### Example Log Output
When using logging your PSR7 messages will be converted into their HTTP request quivalent. The following was generated using `monolog/monlog`, but the general format of a transaction ID followed by a stringed request or response will be consistent regardless of the PSR3 compliant logging solution you use.

```log
[2024-03-24T10:03:48.821264+00:00] Shiptheory.DEBUG: e1232cc3b4683effe04f3e4605a040ea
GET /v1/tags HTTP/1.1
Host: api.shiptheory.com
Accept: application/json
Content-Type: application/json
Authorization: REDACTED
[2024-03-24T10:03:49.399426+00:00] Shiptheory.DEBUG: e1232cc3b4683effe04f3e4605a040ea
HTTP/1.1 200 OK
Server: nginx/1.14.1
Date: Sun, 24 Mar 2024 10:03:18 GMT
Content-Type: application/json; charset=UTF-8
Transfer-Encoding: chunked
Connection: keep-alive
Set-Cookie: AWSALB=h7p5oZdry0zB/l9D2nJyyV627QsNZ8pfT+XBRrjbnlwF4I118DxKxM5o8PvYTcdPT7pJYKlFf0G6A7szXto1OgnJNnBBCFGJxzU0yFfr3cLL/+n0J2L45yCrgVla; Expires=Sun, 31 Mar 2024 10:03:18 GMT; Path=/, AWSALBCORS=h7p5oZdry0zB/l9D2nJyyV627QsNZ8pfT+XBRrjbnlwF4I118DxKxM5o8PvYTcdPT7pJYKlFf0G6A7szXto1OgnJNnBBCFGJxzU0yFfr3cLL/+n0J2L45yCrgVla; Expires=Sun, 31 Mar 2024 10:03:18 GMT; Path=/; SameSite=None; Secure
X-XSS-Protection: 1; mode=block

{"tags":[{"id":4,"name":"My Tag","background_colour":"#008000","text_colour":"#FFF"},{"id":7,"name":"Another Tag","background_colour":"#800080","text_colour":"#FFF"},{"id":326,"name":"A third tag","background_colour":"#FF0000","text_colour":"#FFF"},{"id":329,"name":"Wow a fourth one","background_colour":"#FF8C00","text_colour":"#FFF"}],"pagination":{"page":1,"pages":1,"results":4,"results_per_page":25,"limit":false}}
```

## Using a Shiptheory Partner Tag
*"If you are developing an application that will be used by more than 1 company, or an application that you intend to distribute in anyway, you must include the Shiptheory-Partner-Tag http request header in all of your requests. Please contact Shiptheory support to obtain a partner tag. There is no charge for this, tags are used to provide better support to customers and partners."* - API Docs

In order to add a partner tag to your API requests add it as the fourth argument when instantiating a new ShiptheoryClient.
```php
$client = new ShiptheoryClient($client, $token, $log, 'partner_tag');
```
