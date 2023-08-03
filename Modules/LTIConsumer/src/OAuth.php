<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\LTIOAuth;

use ILIAS\DI\Exceptions\Exception;

/**
 * @file
 * OAuth 1.0 server and client library.
 */

$UseHttpHostInsteadOfServerName = true;
$DefaultSignatureMethodPlaintext = false;

///**
// * OAuth PECL extension includes an OAuth Exception class, so we need to wrap
// * the definition of this class in order to avoid a PHP error.
// */
if (!class_exists('OAuthException')) {
    /*
     * Generic exception class
     */
    class OAuthException extends Exception
    {
        // pass
    }
}

if (!class_exists('OAuthConsumer')) {
    class OAuthConsumer
    {
        /** @var string */
        public string $key;

        /** @var string */
        public string $secret;

        /** @var string|null */
        public ?string $callback_url;


        /**
         * @param string      $key
         * @param string      $secret
         * @param string|null $callback_url
         */
        public function __construct(string $key, string $secret, ?string $callback_url = null)
        {
            $this->key = $key;
            $this->secret = $secret;
            $this->callback_url = $callback_url;
        }


        /**
         * @return string
         */
        public function __toString()
        {
            return "OAuthConsumer[key=$this->key,secret=$this->secret]";
        }
    }
}
if (!class_exists('OAuthToken')) {
    class OAuthToken
    {
        // access tokens and request tokens
        /** @var string */
        public string $key;

        /** @var string */
        public string $secret;

        /** @var callable|null */
        public $callback = null;

        /**
         * @param string $key    = the token
         * @param string $secret = the token secret
         */
        public function __construct(string $key, string $secret)
        {
            $this->key = $key;
            $this->secret = $secret;
        }

        /**
         * generates the basic string serialization of a token that a server
         * would respond to request_token and access_token calls with
         */
        public function to_string(): string
        {
            /** @var string $key */
            $key = OAuthUtil::urlencode_rfc3986($this->key);
            /** @var string $secret */
            $secret = OAuthUtil::urlencode_rfc3986($this->secret);
            return "oauth_token=" . $key .
                "&oauth_token_secret=" . $secret .
                "&oauth_callback_confirmed=true";
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->to_string();
        }
    }
}
/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
if (!class_exists('OAuthSignatureMethod')) {
    abstract class OAuthSignatureMethod
    {
        /**
         * Needs to return the name of the Signature Method (ie HMAC-SHA1)
         */
        abstract public function get_name(): string;

        /**
         * Build up the signature
         * NOTE: The output of this function MUST NOT be urlencoded.
         * the encoding is handled in OAuthRequest when the final
         * request is serialized
         * @param OAuthToken $token|null
         */
        abstract public function build_signature(OAuthRequest $request, OAuthConsumer $consumer, OAuthToken $token): string;

        /**
         * Verifies that a given signature is correct
         * @param OAuthRequest  $request
         * @param OAuthConsumer $consumer
         * @param OAuthToken    $token
         * @param string        $signature
         * @return bool
         */
        public function check_signature(OAuthRequest $request, OAuthConsumer $consumer, OAuthToken $token, string $signature): bool
        {
            $built = $this->build_signature($request, $consumer, $token);

            // Check for zero length, although unlikely here
            if (strlen($built) == 0 || strlen($signature) == 0) {
                return false;
            }

            if (strlen($built) != strlen($signature)) {
                return false;
            }

            // Avoid a timing leak with a (hopefully) time insensitive compare
            $result = 0;
            for ($i = 0; $i < strlen($signature); $i++) {
                $result |= ord($built[$i]) ^ ord($signature[$i]);
            }

            return $result == 0;
        }
    }
}
/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
//if (!class_exists('OAuthSignatureMethod_HMAC_SHA1')) {
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod
{
    public function get_name(): string
    {
        return "HMAC-SHA1";
    }


    /**
     * @param OAuthToken $token|null
     */
    public function build_signature(OAuthRequest $request, OAuthConsumer $consumer, ?OAuthToken $token): string
    {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;

        $key_parts = [
        $consumer->secret,
        ($token) ? $token->secret : ""
        ];

        /** @var array $key_parts */
        $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
}
//}
/**
 * The PLAINTEXT method does not provide any security protection and SHOULD only be used
 * over a secure channel such as HTTPS. It does not use the Signature Base String.
 *   - Chapter 9.4 ("PLAINTEXT")
 */
if (!class_exists('OAuthSignatureMethod_PLAINTEXT')) {
    class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod
    {
        public function get_name(): string
        {
            return "PLAINTEXT";
        }

        /**
         * oauth_signature is set to the concatenated encoded values of the Consumer Secret and
         * Token Secret, separated by a '&' character (ASCII code 38), even if either secret is
         * empty. The result MUST be encoded again.
         *   - Chapter 9.4.1 ("Generating Signatures")
         * Please note that the second encoding MUST NOT happen in the SignatureMethod, as
         * OAuthRequest handles this!
         * @param OAuthRequest    $request
         * @param OAuthConsumer   $consumer
         * @param OAuthToken|null $token
         * @return string
         */
        public function build_signature(OAuthRequest $request, OAuthConsumer $consumer, ?OAuthToken $token): string
        {
            $key_parts = [
            $consumer->secret,
            ($token) ? $token->secret : ""
        ];

            /** @var array $key_parts */
            $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
            $key = implode('&', $key_parts);
            $request->base_string = $key;

            return $key;
        }
    }
}
/**
 * The RSA-SHA1 signature method uses the RSASSA-PKCS1-v1_5 signature algorithm as defined in
 * [RFC3447] section 8.2 (more simply known as PKCS#1), using SHA-1 as the hash function for
 * EMSA-PKCS1-v1_5. It is assumed that the Consumer has provided its RSA public key in a
 * verified way to the Service Provider, in a manner which is beyond the scope of this
 * specification.
 *   - Chapter 9.3 ("RSA-SHA1")
 */
if (!class_exists('OAuthSignatureMethod_RSA_SHA1')) {
    abstract class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod
    {
        public function get_name(): string
        {
            return "RSA-SHA1";
        }


        /**
         * Up to the SP to implement this lookup of keys. Possible ideas are:
         * (1) do a lookup in a table of trusted certs keyed off of consumer
         * (2) fetch via http using a url provided by the requester
         * (3) some sort of specific discovery code based on request
         *
         * Either way should return a string representation of the certificate
         *
         * @param OAuthRequest &$request
         */
        abstract protected function fetch_public_cert(OAuthRequest &$request);


        /**
         * Up to the SP to implement this lookup of keys. Possible ideas are:
         * (1) do a lookup in a table of trusted certs keyed off of consumer
         *
         * Either way should return a string representation of the certificate
         *
         * @param OAuthRequest &$request
         */
        abstract protected function fetch_private_cert(OAuthRequest &$request);


        public function build_signature(OAuthRequest $request, OAuthConsumer $consumer, OAuthToken $token): string
        {
            $base_string = $request->get_signature_base_string();
            $request->base_string = $base_string;

            // Fetch the private key cert based on the request
            $cert = $this->fetch_private_cert($request);

            // Pull the private key ID from the certificate
            $privatekeyid = openssl_get_privatekey($cert);

            // Sign using the key
            openssl_sign($base_string, $signature, $privatekeyid);

            if (PHP_MAJOR_VERSION < 8) {
                // Release the key resource
                openssl_free_key($privatekeyid);
            }
            return base64_encode($signature);
        }


        public function check_signature(OAuthRequest $request, OAuthConsumer $consumer, OAuthToken $token, string $signature): bool
        {
            $decoded_sig = base64_decode($signature);

            $base_string = $request->get_signature_base_string();

            // Fetch the public key cert based on the request
            $cert = $this->fetch_public_cert($request);

            // Pull the public key ID from the certificate
            $publickeyid = openssl_get_publickey($cert);

            // Check the computed signature against the one passed in the query
            $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);

            if (PHP_MAJOR_VERSION < 8) {
                // Release the key resource
                openssl_free_key($publickeyid);
            }

            return $ok == 1;
        }
    }
}
if (!class_exists('OAuthRequest')) {
    class OAuthRequest
    {
        /** @var array */
        protected array $parameters;

        /** @var string */
        protected string $http_method;

        /** @var string */
        protected string $http_url;

        // for debug purposes
        /** @var string|null */
        public ?string $base_string = null;

        /** @var string */
        public static string $version = '1.0';

        /** @var string */
        public static string $POST_INPUT = 'php://input';


        /**
         * @param string     $http_method
         * @param string     $http_url
         * @param array|null $parameters
         * @return void
         */
        public function __construct(string $http_method, string $http_url, ?array $parameters = null)
        {
            $parameters = ($parameters) ? $parameters : [];
            $parameters = array_merge(OAuthUtil::parse_parameters((string) parse_url($http_url, PHP_URL_QUERY)), $parameters);
            $this->parameters = $parameters;
            $this->http_method = $http_method;
            $this->http_url = $http_url;
        }

        /**
         * attempt to build up a request from what was passed to the server
         * @param string|null $http_method
         * @param string|null $http_url
         * @param array|null  $parameters
         * @return OAuthRequest
         */
        public static function from_request(?string $http_method = null, ?string $http_url = null, ?array $parameters = null): OAuthRequest
        {
            $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
            ? 'http'
            : 'https';

            //SPECIAL FOR LTI BEGIN
            global $UseHttpHostInsteadOfServerName;
            if ($UseHttpHostInsteadOfServerName == true) {
                $port = "";
                if ($_SERVER['SERVER_PORT'] != "80" && $_SERVER['SERVER_PORT'] != "443" &&
            strpos(':', $_SERVER['HTTP_HOST']) < 0) {
                    $port = ':' . $_SERVER['SERVER_PORT'] ;
                }
                $http_url = ($http_url) ? $http_url : $scheme .
                '://' . $_SERVER['HTTP_HOST'] .
                $port .
                $_SERVER['REQUEST_URI'];
            } else {
                $http_url = ($http_url) ? $http_url : $scheme .
                '://' . $_SERVER['SERVER_NAME'] .
                ':' .
                $_SERVER['SERVER_PORT'] .
                $_SERVER['REQUEST_URI'];
            }
            //SPECIAL FOR LTI END

            $http_method = ($http_method) ? $http_method : $_SERVER['REQUEST_METHOD'];

            // We weren't handed any parameters, so let's find the ones relevant to
            // this request.
            // If you run XML-RPC or similar you should use this to provide your own
            // parsed parameter-list
            if (!$parameters) {
                // Find request headers
                $request_headers = OAuthUtil::get_headers();

                // Parse the query-string to find GET parameters
                $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

                // It's a POST request of the proper content-type, so parse POST
                // parameters and add those overriding any duplicates from GET
                if ($http_method == "POST"
                && isset($request_headers['Content-Type'])
                && strstr($request_headers['Content-Type'], 'application/x-www-form-urlencoded')
                ) {
                    $post_data = OAuthUtil::parse_parameters(
                        file_get_contents(self::$POST_INPUT)
                    );
                    $parameters = array_merge($parameters, $post_data);
                }

                // We have a Authorization-header with OAuth data. Parse the header
                // and add those overriding any duplicates from GET or POST
                if (isset($request_headers['Authorization'])
                && substr($request_headers['Authorization'], 0, 6) == 'OAuth '
                ) {
                    $header_parameters = OAuthUtil::split_header(
                        $request_headers['Authorization']
                    );
                    $parameters = array_merge($parameters, $header_parameters);
                }
            }

            return new OAuthRequest($http_method, $http_url, $parameters);
        }

        /**
         * pretty much a helper function to set up the request
         * @param OAuthConsumer   $consumer
         * @param OAuthToken|null $token
         * @param string          $http_method
         * @param string          $http_url
         * @param array|null      $parameters
         * @return OAuthRequest
         */
        public static function from_consumer_and_token(OAuthConsumer $consumer, ?OAuthToken $token, string $http_method, string $http_url, ?array $parameters = null): OAuthRequest
        {
            $parameters = ($parameters) ? $parameters : [];
            $defaults = ["oauth_version" => OAuthRequest::$version,
                            "oauth_nonce" => OAuthRequest::generate_nonce(),
                            "oauth_timestamp" => OAuthRequest::generate_timestamp(),
                            "oauth_consumer_key" => $consumer->key];
            if ($token) {
                $defaults['oauth_token'] = $token->key;
            }

            $parameters = array_merge($defaults, $parameters);

            return new OAuthRequest($http_method, $http_url, $parameters);
        }


        public function set_parameter(string $name, string $value, bool $allow_duplicates = true): void
        {
            if ($allow_duplicates && isset($this->parameters[$name])) {
                // We have already added parameter(s) with this name, so add to the list
                if (is_scalar($this->parameters[$name])) {
                    // This is the first duplicate, so transform scalar (string)
                    // into an array so we can add the duplicates
                    $this->parameters[$name] = [$this->parameters[$name]];
                }

                $this->parameters[$name][] = $value;
            } else {
                $this->parameters[$name] = $value;
            }
        }


        /**
         * @return mixed|null
         */
        public function get_parameter(string $name)
        {
            return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
        }


        /**
         * @return array|null
         */
        public function get_parameters(): array
        {
            return $this->parameters;
        }


        public function unset_parameter(string $name): void
        {
            unset($this->parameters[$name]);
        }


        /**
         * The request parameters, sorted and concatenated into a normalized string.
         */
        public function get_signable_parameters(): string
        {
            // Grab all parameters
            $params = $this->parameters;

            // Remove oauth_signature if present
            // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
            if (isset($params['oauth_signature'])) {
                unset($params['oauth_signature']);
            }

            return OAuthUtil::build_http_query($params);
        }


        /**
         * Returns the base string of this request
         *
         * The base string defined as the method, the url
         * and the parameters (normalized), each urlencoded
         * and the concated with &.
         */
        public function get_signature_base_string(): string
        {
            $parts = [
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        ];

            /** @var array $parts */
            $parts = OAuthUtil::urlencode_rfc3986($parts);

            return implode('&', $parts);
        }


        /**
         * just uppercases the http method
         */
        public function get_normalized_http_method(): string
        {
            return strtoupper($this->http_method);
        }


        /**
         * parses the url and rebuilds it to be
         * scheme://host/path
         */
        public function get_normalized_http_url(): string
        {
            $parts = parse_url($this->http_url);

            $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
            $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
            $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
            $path = (isset($parts['path'])) ? $parts['path'] : '';

            if (($scheme == 'https' && $port != '443')
            || ($scheme == 'http' && $port != '80')) {
                $host = "$host:$port";
            }
            return "$scheme://$host$path";
        }


        /**
         * builds a url usable for a GET request
         */
        public function to_url(): string
        {
            $post_data = $this->to_postdata();
            $out = $this->get_normalized_http_url();
            if ($post_data) {
                $out .= '?' . $post_data;
            }
            return $out;
        }


        /**
         * builds the data one would send in a POST request
         */
        public function to_postdata(): string
        {
            return OAuthUtil::build_http_query($this->parameters);
        }

        /**
         * builds the Authorization: header
         * @param string|null $realm
         * @return string
         * @throws OAuthException
         */
        public function to_header(?string $realm = null): string
        {
            $first = true;
            if (!is_null($realm)) {
                /** @var string $realm */
                $realm = OAuthUtil::urlencode_rfc3986($realm);
                $out = 'Authorization: OAuth realm="' . $realm . '"';
                $first = false;
            } else {
                $out = 'Authorization: OAuth';
            }

            foreach ($this->parameters as $k => $v) {
                if (substr($k, 0, 5) != "oauth") {
                    continue;
                }
                if (is_array($v)) {
                    throw new OAuthException('Arrays not supported in headers');
                }
                $out .= ($first) ? ' ' : ',';
                /** @var string $key */
                $key = OAuthUtil::urlencode_rfc3986($k);
                /** @var string $value */
                $value = OAuthUtil::urlencode_rfc3986($v);
                $out .= $key . '="' . $value . '"';
                $first = false;
            }
            return $out;
        }


        /**
         * @return string
         */
        public function __toString()
        {
            return $this->to_url();
        }


        public function sign_request(OAuthSignatureMethod $signature_method, OAuthConsumer $consumer, ?OAuthToken $token): void
        {
            $this->set_parameter(
                "oauth_signature_method",
                $signature_method->get_name(),
                false
            );
            $signature = $this->build_signature($signature_method, $consumer, $token);
            $this->set_parameter("oauth_signature", $signature, false);
        }


        public function build_signature(OAuthSignatureMethod $signature_method, OAuthConsumer $consumer, ?OAuthToken $token): string
        {
            $signature = $signature_method->build_signature($this, $consumer, $token);
            return $signature;
        }


        /**
         * util function: current timestamp
         */
        private static function generate_timestamp(): int
        {
            return time();
        }


        /**
         * util function: current nonce
         */
        private static function generate_nonce(): string
        {
            $mt = microtime();
            $rand = mt_rand();

            return md5($mt . $rand); // md5s look nicer than numbers
        }
    }
}
if (!class_exists('OAuthServer')) {
    class OAuthServer
    {
        /** @var int */
        protected int $timestamp_threshold = 300; // in seconds, five minutes

        /** @var string */
        protected string $version = '1.0'; // hi blaine

        /** @var array */
        protected array $signature_methods = [];

        /** @var OAuthDataStore */
        protected OAuthDataStore $data_store;


        /**
         * @param OAuthDataStore $data_store
         */
        public function __construct(OAuthDataStore $data_store)
        {
            $this->data_store = $data_store;
        }


        public function add_signature_method(OAuthSignatureMethod $signature_method): void
        {
            $this->signature_methods[$signature_method->get_name()] =
            $signature_method;
        }


        // high level functions
        /**
         * process a request_token request
         * returns the request token on success
         */
        public function fetch_request_token(OAuthRequest &$request): OAuthToken
        {
            $this->getVersion($request);

            $consumer = $this->getConsumer($request);

            // no token required for the initial token request
            $token = null;

            $this->checkSignature($request, $consumer, $token);

            // Rev A change
            $callback = $request->get_parameter('oauth_callback');
            $new_token = $this->data_store->new_request_token($consumer, $callback);

            return $new_token;
        }


        /**
         * process an access_token request
         * returns the access token on success
         */
        public function fetch_access_token(OAuthRequest &$request): OAuthToken
        {
            $this->getVersion($request);

            $consumer = $this->getConsumer($request);

            // requires authorized request token
            $token = $this->getToken($request, $consumer, "request");

            $this->checkSignature($request, $consumer, $token);

            // Rev A change
            $verifier = $request->get_parameter('oauth_verifier');
            $new_token = $this->data_store->new_access_token($token, $consumer, $verifier);

            return $new_token;
        }


        /**
         * verify an api call, checks all the parameters
         *
         * @return OAuthConsumer[]|OAuthToken[]
         */
        public function verify_request(OAuthRequest &$request): array
        {
            $this->getVersion($request);
            $consumer = $this->getConsumer($request);
            $token = $this->getToken($request, $consumer, "access");
            $this->checkSignature($request, $consumer, $token);
            return [$consumer, $token];
        }


        // Internals from here
        /**
         * version 1
         */
        private function getVersion(OAuthRequest &$request): string
        {
            $version = $request->get_parameter("oauth_version");
            if (!$version) {
                // Service Providers MUST assume the protocol version to be 1.0 if this parameter is not present.
                // Chapter 7.0 ("Accessing Protected Ressources")
                $version = '1.0';
            }
            if ($version !== $this->version) {
                throw new OAuthException("OAuth version '$version' not supported");
            }
            return $version;
        }

        /**
         * figure out the signature with some defaults
         * @param OAuthRequest $request
         * @return object
         * @throws OAuthException
         */
        private function getSignatureMethod(OAuthRequest $request): object
        {
            $signature_method = $request->get_parameter("oauth_signature_method");

            if (!$signature_method) {
                //Special LTI BEGIN
                global $DefaultSignatureMethodPlaintext;
                if ($DefaultSignatureMethodPlaintext == true) {
                    $signature_method = "PLAINTEXT";
                } else {
                    // According to chapter 7 ("Accessing Protected Ressources") the signature-method
                    // parameter is required, and we can't just fallback to PLAINTEXT
                    throw new OAuthException('No signature method parameter. This parameter is required');
                }
                //Special LTI END
            }

            if (!in_array($signature_method, array_keys($this->signature_methods))) {
                throw new OAuthException(
                    "Signature method '$signature_method' not supported " .
                "try one of the following: " .
                implode(", ", array_keys($this->signature_methods))
                );
            }
            return $this->signature_methods[$signature_method];
        }


        /**
         * try to find the consumer for the provided request's consumer key
         *
         * @param OAuthRequest $request
         */
        private function getConsumer(OAuthRequest $request): OAuthConsumer
        {
            $consumer_key = $request->get_parameter("oauth_consumer_key");

            if (!$consumer_key) {
                throw new OAuthException("Invalid consumer key");
            }

            $consumer = $this->data_store->lookup_consumer($consumer_key);
            if (!$consumer) {
                throw new OAuthException("Invalid consumer");
            }

            return $consumer;
        }


        /**
         * try to find the token for the provided request's token key
         *
         * @throws OAuthException
         */
        private function getToken(OAuthRequest $request, OAuthConsumer $consumer, string $token_type = "access"): OAuthToken
        {
            $token_field = $request->get_parameter('oauth_token');

            if (!empty($token_field)) {
                $token = $this->data_store->lookup_token($consumer, $token_type, $token_field);
                if (!$token) {
                    throw new OAuthException('Invalid ' . $token_type . ' token: ' . $token_field);
                }
            } else {
                $token = new OAuthToken('', '');
            }
            return $token;
        }


        /**
         * all-in-one function to check the signature on a request
         * should guess the signature method appropriately
         *
         * @param OAuthRequest $request
         * @param OAuthConsumer $consumer
         * @param OAuthToken $token|null
         * @throws OAuthException
         */
        private function checkSignature(OAuthRequest $request, OAuthConsumer $consumer, OAuthToken $token = null): void
        {
            // this should probably be in a different method
            $timestamp = (int) $request->get_parameter('oauth_timestamp');
            $nonce = $request->get_parameter('oauth_nonce');

            $this->checkTimestamp($timestamp);
            $this->checkNonce($consumer, $token, $nonce, $timestamp);

//            $signature_method = 'OAuthSignatureMethod_' . $this->getSignatureMethod($request);
            $signature_method = $this->getSignatureMethod($request); //check UK
            /** @psalm-suppress InvalidStringClass */
            $method = new $signature_method();

            $signature = $request->get_parameter('oauth_signature');
//            $valid_sig = $method->checkSignature( //check UK
            $valid_sig = $method->check_signature(
                $request,
                $consumer,
                $token,
                $signature
            );

            if (!$valid_sig) {
                throw new OAuthException("Invalid signature");
            }
        }


        /**
         * check that the timestamp is new enough
         *
         * @throws OAuthException
         */
        private function checkTimestamp(?int $timestamp): void
        {
            if (!$timestamp) {
                throw new OAuthException(
                    'Missing timestamp parameter. The parameter is required'
                );
            }

            // verify that timestamp is recentish
            $now = time();
            if (abs($now - $timestamp) > $this->timestamp_threshold) {
                throw new OAuthException(
                    "Expired timestamp, yours $timestamp, ours $now"
                );
            }
        }


        /**
         * check that the nonce is not repeated
         *
         * @throws OAuthException
         */
        private function checkNonce(OAuthConsumer $consumer, ?OAuthToken $token, string $nonce, int $timestamp): void
        {
            if (!$nonce) {
                throw new OAuthException(
                    'Missing nonce parameter. The parameter is required'
                );
            }

            // verify that the nonce is uniqueish
            $found = $this->data_store->lookup_nonce(
                $consumer,
                $token,
                $nonce,
                $timestamp
            );
            if ($found) {
                throw new OAuthException("Nonce already used: $nonce");
            }
        }
    }
}
if (!class_exists('OAuthDataStore')) {
    abstract class OAuthDataStore
    {
        abstract public function lookup_consumer(string $consumer_key);


        abstract public function lookup_token(OAuthConsumer $consumer, string $token_type, string $token);


        abstract public function lookup_nonce(OAuthConsumer $consumer, ?OAuthToken $token, string $nonce, int $timestamp);


        /**
         * @param callable|null $callback
         */
        abstract public function new_request_token(OAuthConsumer $consumer, ?string $callback = null);

        /**
         * @param OAuthToken    $token
         * @param OAuthConsumer $consumer
         * @param string|null   $verifier
         */
        abstract public function new_access_token(OAuthToken $token, OAuthConsumer $consumer, ?string $verifier = null);
    }
}
if (!class_exists('OAuthUtil')) {
    class OAuthUtil
    {
        /**
         * @param mixed $input
         * @return string|array
         */
        public static function urlencode_rfc3986($input)
        {
            if (is_array($input)) {
                return array_map(['ILIAS\LTIOAuth\OAuthUtil', 'urlencode_rfc3986'], $input);
            } elseif (is_scalar($input)) {
                return str_replace(
                    '+',
                    ' ',
                    str_replace('%7E', '~', rawurlencode(strval($input)))
                );
            } else {
                return '';
            }
        }


        /**
         * This decode function isn't taking into consideration the above
         * modifications to the encoding process. However, this method doesn't
         * seem to be used anywhere so leaving it as is.
         */
        public static function urldecode_rfc3986(string $string): string
        {
            return urldecode($string);
        }


        /**
         * Utility function for turning the Authorization: header into
         * parameters, has to do some unescaping
         * Can filter out any non-oauth parameters if needed (default behaviour)
         * May 28th, 2010 - method updated to tjerk.meesters for a speed improvement.
         *                  see http://code.google.com/p/oauth/issues/detail?id=163
         *
         * @return array<int|string, string>
         */
        public static function split_header(string $header, bool $only_allow_oauth_parameters = true): array
        {
            $params = [];
            if (preg_match_all(
                '/(' . ($only_allow_oauth_parameters ? 'oauth_' : '') . '[a-z_-]*)=(:?"([^"]*)"|([^,]*))/',
                $header,
                $matches
            )) {
                foreach ($matches[1] as $i => $h) {
                    $params[$h] = OAuthUtil::urldecode_rfc3986(empty($matches[3][$i]) ? $matches[4][$i] : $matches[3][$i]);
                }
                if (isset($params['realm'])) {
                    unset($params['realm']);
                }
            }
            return $params;
        }


        /**
         * helper to try to sort out headers for people who aren't running apache
         *
         * @return array<string, mixed>
         */
        public static function get_headers(): array
        {
            if (function_exists('apache_request_headers')) {
                // we need this to get the actual Authorization: header
                // because apache tends to tell us it doesn't exist
                $headers = getallheaders();

                // sanitize the output of apache_request_headers because
                // we always want the keys to be Cased-Like-This and arh()
                // returns the headers in the same case as they are in the
                // request
                $out = [];
                foreach ($headers as $key => $value) {
                    $key = str_replace(
                        " ",
                        "-",
                        ucwords(strtolower(str_replace("-", " ", $key)))
                    );
                    $out[$key] = $value;
                }
            } else {
                // otherwise we don't have apache and are just going to have to hope
                // that $_SERVER actually contains what we need
                $out = [];
                if (isset($_SERVER['CONTENT_TYPE'])) {
                    $out['Content-Type'] = $_SERVER['CONTENT_TYPE'];
                }
                if (isset($_ENV['CONTENT_TYPE'])) {
                    $out['Content-Type'] = $_ENV['CONTENT_TYPE'];
                }

                foreach ($_SERVER as $key => $value) {
                    if (substr($key, 0, 5) == "HTTP_") {
                        // this is chaos, basically it is just there to capitalize the first
                        // letter of every word that is not an initial HTTP and strip HTTP
                        // code from przemek
                        $key = str_replace(
                            " ",
                            "-",
                            ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
                        );
                        $out[$key] = $value;
                    }
                }
                // The "Authorization" header may get turned into "Auth".
                if (isset($out['Auth'])) {
                    $out['Authorization'] = $out['Auth'];
                }
            }
            return $out;
        }


        /**
         * This function takes a input like a=b&a=c&d=e and returns the parsed
         * parameters like this
         * array('a' => array('b','c'), 'd' => 'e')
         *
         * @return mixed[]
         */
        public static function parse_parameters(?string $input): array
        {
            if (!isset($input) || $input == "") {
                return [];
            }

            $pairs = explode('&', $input);

            $parsed_parameters = [];
            foreach ($pairs as $pair) {
                $split = explode('=', $pair, 2);
                $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
                $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

                if (isset($parsed_parameters[$parameter])) {
                    // We have already recieved parameter(s) with this name, so add to the list
                    // of parameters with this name

                    if (is_scalar($parsed_parameters[$parameter])) {
                        // This is the first duplicate, so transform scalar (string) into an array
                        // so we can add the duplicates
                        $parsed_parameters[$parameter] = [$parsed_parameters[$parameter]];
                    }

                    $parsed_parameters[$parameter][] = $value;
                } else {
                    $parsed_parameters[$parameter] = $value;
                }
            }
            return $parsed_parameters;
        }


        /**
         * @param mixed[] $params
         */
        public static function build_http_query(array $params): string
        {
            if (!$params) {
                return '';
            }

            // Urlencode both keys and values
            /** @var array $keys */
            $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
            /** @var array $values */
            $values = OAuthUtil::urlencode_rfc3986(array_values($params));
            $params = array_combine($keys, $values);

            // Parameters are sorted by name, using lexicographical byte value ordering.
            // Ref: Spec: 9.1.1 (1)
            uksort($params, 'strcmp');

            $pairs = [];
            foreach ($params as $parameter => $value) {
                if (is_array($value)) {
                    // If two or more parameters share the same name, they are sorted by their value
                    // Ref: Spec: 9.1.1 (1)
                    // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                    sort($value, SORT_STRING);
                    foreach ($value as $duplicate_value) {
                        $pairs[] = $parameter . '=' . $duplicate_value;
                    }
                } else {
                    $pairs[] = $parameter . '=' . $value;
                }
            }
            // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
            // Each name-value pair is separated by an '&' character (ASCII code 38)
            return implode('&', $pairs);
        }
    }
}
