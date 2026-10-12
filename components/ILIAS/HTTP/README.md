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

# DropInReplacements
With ILIAS 8, the Technical Board decided to replace the [`Superglobals`](https://www.php.net/manual/en/language.variables.superglobals.php)
`$_GET`, `$_POST`, `$_COOKIE` and `$_REQUEST` with so-called `SuperGlobalDropInReplacement` instances.
These are `ArrayAccess` wrappers for the respective `Superglobals`. They use the [`Refinery`](../Refinery/README.md)
to sanitize values on readout via the `->kindlyTo()->string()` transformation.
`SuperGlobalDropInReplacement` also prevents values in the `Superglobals` from being manually assigned or
overwritten, since this would violate the immutability of HTTP request values.
Globally replacing the `Superglobals` causes problems with some third-party libraries that require a plain
`array` rather than an `ArrayAccess` object (currently known for `SimpleSAMLphp`). Therefore, the replacement
can be disabled via an ini setting in `client.ini.php`.

As of ILIAS 11, this setting is only applied when `DEVMODE` is enabled.

```
[server]
prevent_super_global_replacement = 1
```

Furthermore, the `SuperGlobalDropInReplacement` behave in such a way that overwriting a value
in one of the `Superglobals` leads to a `\OutOfBoundsException`.

# postMessage Channel: Talking to Other Origins

Some ILIAS pages show content of another origin in an iframe: user content on
the content domain of
[User Content Isolation](../FileDelivery/README.md#irss-user-content-isolation)
(SCORM packages, HTML learning modules), or editors of external servers
embedded via WOPI. The browser blocks any direct access between windows of
different origins (reading `window.parent.something`,
`iframe.contentWindow.document`, ...). The only way to communicate is
`postMessage`. All these cases share the same requirements, which
`il.HTTP.Channel` covers:

* messages are only accepted from the expected window **and** origin,
* outgoing messages are addressed to that origin, never to `*`,
* request/response with correlation ids and timeouts, plus fire-and-forget
  events,
* an exchangeable codec for the format on the wire.

The script is `public/assets/js/postmessage-channel.js`. It creates its own
namespace, so it can also be loaded by static pages of another origin (e.g. on
the content domain) that load no other ILIAS script.

## Usage

```js
// in the ILIAS page; iframe shows content of the content domain
const channel = new il.HTTP.Channel(
  window,                        // own window, messages are received here
  () => iframe.contentWindow,    // the other end
  'https://content.example.org', // its origin ('*', 'null' and '' are refused)
  'my-channel',                  // both ends use the same name
);
channel.on('progress', (payload) => store(payload)); // the return value answers requests
channel.send('configure', { level: 2 });             // event, no answer
channel.request('state', null, 5000)                 // resolves with the answer
  .then((state) => { /* ... */ });
channel.destroy();                                   // removes the listener
```

The other end (e.g. a page on the content domain) creates a channel the same
way with `() => window.parent` and the origin of the ILIAS page.

## Message format and codec

By default (`il.HTTP.Channel.envelopeCodec`) messages are plain objects:

```js
{
  'il-postmessage': 1,  // marker and version; other messages are ignored
  channel: 'my-channel',
  type: 'progress',
  payload: { ... },     // structured clone, no functions or DOM nodes
  id: 7,                // requests only
  re: 7,                // responses only: id of the request
  error: '...',         // failed requests only
}
```

A handler that throws, or a missing handler, rejects the request on the other
side. Requests time out after 10 s by default (`0` waits forever).

Protocols defined by others (e.g. WOPI, which exchanges JSON strings with a
`MessageId`) are used by passing a codec as fifth argument:

```js
const codec = {
  encode: (channel, message) => JSON.stringify({ MessageId: message.type, Values: message.payload }),
  decode: (channel, data) => { /* return {type, payload} or null to ignore the data */ },
};
new il.HTTP.Channel(window, () => editor.contentWindow, editorOrigin, 'wopi', codec);
```

## Security notes

* The origin and window checks only ensure **who** sent a message. Content of
  another origin (user content in particular) is untrusted, so is everything it
  sends: every handler of
  an ILIAS page is a trust boundary. Accept only what the content may change
  anyway, take everything else from the page's own state, and never insert
  received strings as HTML.
* Everything sent to the content is readable by it. Never send session ids,
  CSRF tokens or data of other users.
* All user content shares the content domain of User Content Isolation and is
  not isolated from each other. Pages that give content of the content domain access to something
  (like the SCORM API) should send `Cross-Origin-Opener-Policy: same-origin`, so
  content opening them with `window.open()` gets no handle to their frames.
* Content that has to survive closing the window cannot rely on the ILIAS page
  still processing messages; it has to send its last data itself (e.g. with
  `fetch(..., {keepalive: true, credentials: 'omit'})` to an endpoint that does
  not need the session).

## Consumers

* SCORM 1.2 and 2004 players: the runtime runs in a guest frame next to the SCO,
  see `components/ILIAS/ScormAicc/README.md`.

## Building and testing

Sources in `resources/js/postmessage/src`, built with `npx rollup -c` in
`resources/js/postmessage` (the result in `dist/` is committed). Tests:
`tests/Client/PostMessage` (`npm test`).
