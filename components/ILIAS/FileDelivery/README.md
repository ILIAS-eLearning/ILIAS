# Delivering Files to Browser

This service is the central place to deliver files to the browser. The new server in /src will completely replace the
old implementations in the future, currently they are used in parallel or as wrappers.

## Usage

The service is accessible via the DIC as follows:

```php
global $DIC;
/** @var \ILIAS\FileDelivery\Services $file_delivery  */
$file_delivery = $DIC['file_delivery'];
```

you get an instance of `\ILIAS\FileDelivery\Services` which contains other functions besides the actual delivery of
files, see below.

The service then is used as follows to deliver FileStreams:

```php
global $DIC;
/** @var \ILIAS\FileDelivery\Services $file_delivery  */
$file_delivery = $DIC['file_delivery'];
$file_stream = ILIAS\Filesystem\Stream\Streams::ofResource(
    fopen('/path/to/file', 'rb')
);

// downloads the file
$file_delivery->delivery()->attached(
    $file_stream,
    'filename.txt',
);

// delivers the file inline
$file_delivery->delivery()->inline(
    $file_stream,
    'filename.png',
);

```

### Legacy usage

In many cases, files (in the sense of paths) are still delivered. With the increasing use of IRSS, however, these cases
will become fewer and should eventually disappear altogether.
The delivery of a file happens as follows:

```php
global $DIC;
/** @var \ILIAS\FileDelivery\Services $file_delivery  */
$file_delivery = $DIC['file_delivery'];
$file_path = '/path/to/file';

// downloads the file
$file_delivery->legacyDelivery()->attached(
    $file_path,
    'filename.txt',
);

// delivers the file inline
$file_delivery->legacyDelivery()->inline(
    $file_path,
    'filename.png',
);

```

# Signed Delivery

The IRSS uses exclusively streamss for files and flavours, from ILIAS 9 also for structured data (e.g. later for HTML
learning modules or SCORM learning modules). Up to ILIAS 9 these dtaeias are also delivered via a special mechanism
through the WebAccessChecker. Now streams can be delivered very easily via a token-based way to protect the files. This
will be the general way to deliver the files in the future and will replace the WebAccessChecker completely in the
medium term.

## How does the Signing work in general?

The project ["It's Dangerous"](https://itsdangerous.palletsprojects.com/en/2.1.x/) was used as inspiration for the
implementation. The machnism can be summarized simply:

A token is created for a payload of simple data. In a first step, the payload is serialized for further processing. This
string is optionally supplemented with a validity timestamp. This data is then signed with a Machnism. For the
signature, a secret key as well as a salt is used depending on the use case.
The serialized payload is merged with the signature, compressed, and formatted for embedding in URLs.
Once a token is then to be verified, the following happens: URl preparation is reversed, and the data is decompressed.
The result consists of the serialized payload and the signature. A new signature is now made for the payload and
compared with the one supplied. If these are identical, it is certain that the dtaen are valid and have not been
manipulated.
The mechanism uses a key rotation, currently always 5 keys are kept. with a composer install / dump-autoload a new key
is generated in each case.

### Example:

```php
use ILIAS\FileDelivery\Token\Serializer\JSONSerializer;
use ILIAS\FileDelivery\Token\Signer\KeyRotatingSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\HMACSigner;
use ILIAS\FileDelivery\Token\Signer\Algorithm\SHA1;
use ILIAS\FileDelivery\Token\Signer\Key\Signing\HMACSigningKeyGenerator;
use ILIAS\FileDelivery\Token\Signer\Salt\Salt;
use ILIAS\FileDelivery\Token\Compression\DeflateCompression;
use ILIAS\FileDelivery\Token\Transport\URLSafeTransport;

// This is the payload we want to sign. It should use a "personal" component, e.g. the user ID, and a "global" component, e.g. the path to the file.
 
$payload = [
    'user_id' => 123,
    'uri' => '/path/to/file',
];

// Using a `Serializer` we can serialize the payload to a string. The `JSONSerializer` is used here, but you can also use the `PHPSerializer` or implement your own.
$serializer = new JSONSerializer();
$serialized_payload = $serializer->serializePayload($payload);
// $serialized_payload = '{"user_id":123,"uri":"/path/to/file"}'

// Since we want the token to expire after a certain time, we add a timestamp to the payload. The timestamp is the current time plus the desired lifetime of the token in seconds.
$signable_payload = $serialized_payload.'|'.(time() + 3600);
// $signable_payload = '{"user_id":123,"uri":"/path/to/file"}|1631631631'

// new we can sign the payload. The `KeyRotatingSigner` is used here. It uses a Key Rotation to sign the payload. The `KeyRotation` is used to store the keys. The `SecretKey` is used to store the actual keys. The `HMACSigner` is used to sign the payload. The `HMACSigningKeyGenerator` is used to generate the keys to sign. Using SHA1 as the algorithm, the `HMACSigner` will use the SHA1 algorithm to sign the payload. The `HMACSigningKeyGenerator` will use the SHA1 algorithm to generate the keys.

$signer = new KeyRotatingSigner(
    new SecretKeyRotation(
        new SecretKey('key_one'),
        new SecretKey('key_two')
        new SecretKey('key_three')
    ),
    new HMACSigner(new SHA1())
    new HMACSigningKeyGenerator(new SHA1())
);

$signature = $signer->sign($signable_payload, new Salt('salt'));
// $signature = '...';

// we can now merge the payload and the signature. 
$signed_payload = $signable_payload.'|'.$signature;
// $signed_payload = '{"user_id":123,"uri":"/path/to/file"}|1631631631|...';

// now we can compress the payload. The `DeflateCompression` is used here, but you can also implement your own.
$compression = new DeflateCompression();
$compressed_payload = $compression->compress($signed_payload);

// now we can encode the payload. The `URLSafeTransport` is used here since we use the tokens in URLs.
$transport = new URLSafeTransport();
$token = $transport->encode($compressed_payload);

```

Now, when a token is to be verified, the following happens:

```php
use ILIAS\FileDelivery\Token\Transport\URLSafeTransport;
use ILIAS\FileDelivery\Token\Compression\DeflateCompression;
use ILIAS\FileDelivery\Token\Signer\KeyRotatingSigner;
use ILIAS\FileDelivery\Token\Signer\Salt\Salt;
use ILIAS\FileDelivery\Token\Serializer\JSONSerializer;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKey;
use ILIAS\FileDelivery\Token\Signer\HMACSigner;
use ILIAS\FileDelivery\Token\Signer\Key\Signing\HMACSigningKeyGenerator;
use ILIAS\FileDelivery\Token\Signer\Algorithm\SHA1;

// this is the token
$token = '...';

// first we decode the token
$transport = new URLSafeTransport();
$decoded_token = $transport->readFromTransport($token);

// after that, we decompress the token
$compression = new DeflateCompression();
$decompressed_token = $compression->decompress($decoded_token);

$parts = explode('|', $decompressed_token);
$serialized_payload = $split_data[0] ?? '';
$validity = $split_data[1] ?? '';
$signature = $split_data[2] ?? '';
$payload_with_validity = $serialized_payload . self::SEPARATOR . $validity;

// now we can verify the token by signing the payload and comparing the signature
$signer = new KeyRotatingSigner(
    new SecretKeyRotation(
        new SecretKey('key_one'),
        new SecretKey('key_two')
        new SecretKey('key_three')
    ),
    new HMACSigner(new SHA1())
    new HMACSigningKeyGenerator(new SHA1())
);

// `verify` loops all keys from the keyrotation and signs the payload. if the signature matches, the payload is valid.
$is_valid = $signer->verify($payload_with_validity, $signature, new Salt('salt'));

if($is_valid) {
    $serializer = new JSONSerializer();
    $payload = $serializer->unserializePayload($serialized_payload);
    // $payload = ['user_id' => 123, 'uri' => '/path/to/file']
}else {
    // the token is invalid
    $payload = null;
}
```

Since this process is quite complex, this is simply offered to generate appropriate tokens for FileStreams. You get a
ready URL, which can then deliver the file, if the token is valid:

```php
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileDelivery\Delivery\Disposition;

global $DIC;
$stream = Streams::ofResource(fopen('/path/to/file', 'r'));
$uri = $DIC->fileDelivery()->buildTokenURL(
    $stream,
    'Download-Filename.png',
    Disposition::INLINE
    123 // user_id
    6 // valid for at least 6 hours
)

// $uri = 'http://trunk.ilias.localhost/src/FileDelivery/deliver.php/LY3NasMwEITy[...]RFiKcUmOmJx8Ac'
```

This mechanism is then simply used, for example, to deliver structured resources (container resources). A URL on HTML
learning module can then look like this:

```
http://trunk.ilias.localhost/src/FileDelivery/deliver.php/LY3NasMwEITy[...]RFiKcUmOmJx8Ac/index.html
```

> Important: Do not combine the Singed Delivery with other mechanisms such 
> as the [WebAcceessChecker](../WebAccessChecker/README.md) 

# IRSS User Content Isolation

User-uploaded content (and derived files such as previews) can contain active
markup (SVG, HTML, …). Serving it from the same origin as the application allows
attacks such as stored XSS, content sniffing or canvas exfiltration against the
logged-in session. *User Content Isolation* serves all IRSS assets from a
dedicated, cookie-less **content domain** while the application keeps running on
the ILIAS domain (OWASP: *"use an isolated server with a different domain to
serve uploaded files"*).

This is **proxy mode**: the content domain is an additional vhost pointing at the
*same* ILIAS installation. Signed token delivery (`deliver.php`) is unchanged;
only the host that the embed URLs point to differs.

## What ILIAS enforces when the feature is active

* `Services::getBaseURI()` generates all IRSS embed URLs against the content
  domain instead of the request host — consumers need no changes.
* `deliver.php` (`StreamDelivery::deliverFromToken()`) serves assets **only** when
  reached via the content host; a request on the ILIAS host returns `404`.
* `LegacyDelivery` refuses to serve on the content host (`404`) — the content
  domain is reserved for signed token delivery.
* `HttpPathBuilder` rejects any attempt to load the regular ILIAS application via
  the content domain.
* Delivery responses are hardened with `X-Content-Type-Options: nosniff`,
  `Cross-Origin-Resource-Policy: cross-origin`, `Referrer-Policy: no-referrer`
  and a CORS `Access-Control-Allow-Origin` restricted to the ILIAS domain (with
  `Vary: Origin`). No credentials are allowed, so session cookies are never used
  for asset requests.

## Configuration (Setup only — no GUI)

Per JourFixe decision the feature is configured exclusively through the Setup so
that installations can be made secure-by-default via CLI. The settings are
written to a static PHP artefact (`public/data/isolation.php`) and read at
runtime without any DB access.

Add the following block to your setup `config.json`. The top-level key is
`content_isolation`:

```json
{
    "content_isolation": {
        "activated": true,
        "content_domain": "content.example.org"
    }
}
```

* `content_domain` is the dedicated origin user content is served from. Either a
  bare host (`content.example.org`, normalised to `https://`) or a full `http(s)`
  origin (`scheme://host[:port]`, no userinfo/path/query) is accepted. It is
  **required** and must differ from the ILIAS origin when `activated` is `true`,
  otherwise the Setup aborts with a clear error.
* The **ILIAS domain** (used as the CORS `Access-Control-Allow-Origin` for asset
  requests) is **not** configured here. It is derived from the installed
  `http.path` at setup time and baked into the artefact, so it never has to be
  repeated in this block and the runtime never reads `ilias.ini.php` to obtain it.
  Reconfiguring `http.path` and re-running the Setup updates it automatically.
* `activated: false` (the default) keeps the previous behaviour: assets are
  served from the ILIAS domain via the regular signed delivery.

Run `setup install`/`setup update` to write the artefact. Because the ILIAS domain
is taken from `http.path`, run the FileDelivery setup *after* `http.path` is
configured (the Setup enforces this ordering via objective preconditions).

> **`http.allowed_hosts` is not involved.** The content domain does **not** need to
> be added to `http.allowed_hosts`. Asset delivery runs through `deliver.php`, which
> boots via the FileDelivery component entry point and never reaches the
> `allowed_hosts` check in `HttpPathBuilder`. Keeping the content host out of
> `allowed_hosts` is in fact intended: the regular application must not be reachable
> under the content domain.

## Operational requirements

* **DNS/TLS/vhost**: the content domain needs its own DNS record and TLS
  certificate and must be served by a vhost pointing at the *same* ILIAS
  installation. Use a domain clearly different from the ILIAS domain
  (e.g. `iliascontent.de` vs `ilias.de`).
* **Session cookie must stay host-only**: ILIAS sets the session cookie without a
  `Domain` attribute (`IL_COOKIE_DOMAIN = ''`), so it is never sent to the
  content host. Do **not** configure a shared parent cookie domain — that would
  leak the session to the content domain and defeat the isolation.
* **Extra headers**: ILIAS already emits the headers needed for image/asset
  embedding. For cross-origin embedding of `.css`/`.js`/`.html` you may still
  need to set the corresponding headers on your web server / the content vhost.

## Example web server setup (one possible approach)

The content domain is not a second installation: it points at the **same**
document root and the same PHP as the ILIAS domain, and the isolation is enforced
in PHP by host name. So in the simplest case you just let your existing ILIAS
vhost answer both host names — no second vhost, no extra rules. Point the content
domain at the same server via DNS and use a certificate covering both names.

Apache — add one line to the existing vhost:

```apache
ServerName   ilias.example.org
ServerAlias  content.example.org
```

nginx — add the host to the existing `server_name`:

```nginx
server_name ilias.example.org content.example.org;
```

Everything else stays as it is. Depending on your setup (reverse proxy,
container, where TLS is terminated) the details will differ — the only
requirement is that the content domain reaches the same ILIAS installation.
