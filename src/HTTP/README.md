# HTTP Service for ILIAS

This package provides a fully PSR-7 compliant request and response handling
for ILIAS. The package also provides a convenient way to handle cookies
of the response.

## Request

### Description
The request is used to fetch data about the actual ongoing request.

### Usage
```php
<?php
$dic = $GLOBALS["DIC"];

use Psr\Http\Message\ServerRequestInterface;

/**
 * @var ServerRequestInterface $request 
 */
$request = $dic->http()->request();

//get server parameters
$request->getServerParams();

//get a key value pair for all cookies
$request->getCookieParams();

//get the parsed query parameters if there are any.
$request->getQueryParams();

//get array of UploadedFileInterface
$request->getUploadedFiles();

//get parsed body or null if the body is not present.
$request->getParsedBody();

//get all custom attributes derived from the request.
$request->getAttributes();

//get a specific attribute with the given name or the default value if the value is not found.
$request->getAttribute($name, $default = null);
```
## Response
### Description
The response object which is stored in the ILIAS DIC is used to build the http response.
This response is rendered by ilCtrl at the end of the request.

### Caveats
The response is immutable therefore to save the changes made to the response must be saved over the
 saveResponse method. It is generally advisable that the response never is stored in a class wide scope.
 
### Usage
```php
<?php
$dic = $GLOBALS["DIC"];

use \Psr\Http\Message\ResponseInterface;

/**
 * @var ResponseInterface $response
 */
$response = $dic->http()->request();

//get the current status code
$response->getStatusCode();

//get the reason phrase for the current status code
$response->getReasonPhrase();

//get the http protocol version
$response->getProtocolVersion();

//get all http header as array
//array structure -> [key: [value, value], key: [value]]
$response->getHeaders();

//check if the specific header exists
$response->hasHeader($name);
 
//get a specific header by name
$response->getHeader($name);

//coma separated values for the given header.
$response->getHeaderLine($name);

//returns the body as a stream.
$response->getBody();

//setts the new status code and returns the new response object.
//The reason phrase will be automatically set if no phrase is set by the caller.
$response = $response->withStatus($code, $reasonPhrase = '');
$dic->http()->saveResponse($response);

//creates a new response with the new http protocol version.
$response->withProtocolVersion($version);
$dic->http()->saveResponse($response);

//replace a given header by name.
$response->withHeader($name, $value);
$dic->http()->saveResponse($response);

//adds a value to the given header
$response->withAddedHeader($name, $value);
$dic->http()->saveResponse($response);

//removes a given header by name.
$response->withoutHeader($name);
$dic->http()->saveResponse($response);

//set a new body stream must implement PSR-7 StreamInterface
$response->withBody($body);
$dic->http()->saveResponse($response);
```


## Cookie Handling
### Description
The cookie service is an immutable helper class which reads the Set-Cookie header of the given response
and can render the newly set cookies back.

### Caveats
Be aware that the cookie jar reads the cookies at creation time of the jar.
After the new cookie header is rendered into the response the response must be saved.
See the caveats section of the response for further information.
 
### Usage

#### Add new Cookie 
```php
<?php
use \ILIAS\HTTP\Cookies\CookieFactoryImpl;
use \ILIAS\HTTP\Cookies\CookieJar;

//get the cookie jar
$dic = $GLOBALS["DIC"];

/**
 * @var CookieJar $cookieJar
 */
$cookieJar = $dic->http()->cookieJar();

//create a new cookie factory
$cookieFactory = new CookieFactoryImpl();

//create a cookie
$cookie = $cookieFactory->create("CookieName", "CookieValue");

//set the cookie path
$cookie->withPath('/');

//render the cookies back into the response.
$response = $cookieJar
    ->with($cookie)
    ->renderIntoResponseHeader($dic->http()->response());

//save the response back into the global http state.
$dic->http()->saveResponse($response);
```

#### Expire Cookie

```php
<?php
use \ILIAS\HTTP\Cookies\CookieFactoryImpl;
use \ILIAS\HTTP\Cookies\CookieJar;

//get the cookie jar
$dic = $GLOBALS["DIC"];

/**
 * @var CookieJar $cookieJar
 */
$cookieJar = $dic->http()->cookieJar();

//create a new cookie factory
$cookieFactory = new CookieFactoryImpl();

//create an expired cookie
$cookie = $cookieFactory->createExpired("CookieName");

//set the cookie path
$cookie
    ->withPath('/');

//render the cookies back into the response.
$response = $cookieJar
    ->with($cookie)
    ->renderIntoResponseHeader($dic->http()->response());

//save the response back into the global http state.
$dic->http()->saveResponse($response);
```

## Used Libraries
### Guzzle/psr7
#### Why ?
The guzzle psr7 library is one of the most used psr-7 implementation with over 15 million downloads via packagist.org.
The code base is actively maintained via github.

### ralouphie/getallheaders
#### Why ?
This library is used as polyfill for the apache specific getallheader method under nginx.
The HTTP service doesn't directly rely on this method but the fromGlobals method of the guzzle/psr7 library.

### dflydev/fig-cookies
#### Why ?
The fig-cookies library is a really simple library and is build to directly operate with psr-7 responses.
There are no calls to setcookie and no use of sessions or the $_COOKIES supper global.
The project is also actively maintained.

### psr/http-message
#### Why ?
The http-message package contains the specified interfaces of the php-fig which defined psr-7.



