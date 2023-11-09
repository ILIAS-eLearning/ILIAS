<?php

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

namespace ILIAS\LTI\ToolProvider\Http;

use ILIAS\LTI\ToolProvider\Util;

/**
 * Class to represent an HTTP message request
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class HttpMessage
{
    /**
     * True if message was processed successfully.
     *
     * @var bool    $ok
     */
    public bool $ok = false;

    //UK:added
    public object $responseJson;

    /**
     * Request body.
     *
     * @var string|null $request
     */
    public ?string $request = null;

    /**
     * Request headers.
     *
     * @var string|array $requestHeaders
     */
    public $requestHeaders = '';

    /**
     * Response body.
     *
     * @var string|null $response
     */
    public ?string $response = null;

    /**
     * Response headers.
     *
     * @var string|array $responseHeaders
     */
    public $responseHeaders = '';

    /**
     * Relative links in response headers.
     *
     * @var array $relativeLinks
     */
    public array $relativeLinks = array();

    /**
     * Status of response (0 if undetermined).
     *
     * @var int $status
     */
    public int $status = 0;

    /**
     * Error message
     *
     * @var string $error
     */
    public string $error = '';

    /**
     * Request URL.
     *
     * @var string|null $url
     */
    private ?string $url = null;

    /**
     * Request method.
     *
     * @var string $method
     */
    private ?string $method = null;

    /**
     * The client used to send the request.
     *
     * @var ClientInterface $httpClient
     */
    private static ?ClientInterface $httpClient = null; //changed ...= null

    /**
     * Class constructor.
     * @param string      $url    URL to send request to
     * @param string      $method Request method to use (optional, default is GET)
     * @param mixed       $params Associative array of parameter values to be passed or message body (optional, default is none)
     * @param string|null $header Values to include in the request header (optional, default is none)
     */
    public function __construct(string $url, string $method = 'GET', $params = null, string $header = null)
    {
        $this->url = $url;
        $this->method = strtoupper($method);
        if (is_array($params)) {
            $this->request = http_build_query($params);
        } else {
            $this->request = $params;
        }
        if (!empty($header)) {
            $this->requestHeaders = explode("\n", $header);
        }
    }

    /**
     * Get the target URL for the request.
     *
     * @return string|null Request URL  //UK: Changed from string to string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Get the HTTP method for the request.
     *
     * @return string|null Message method  //UK: Changed from string to string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Set the HTTP client to use for sending the message.
     * @param ClientInterface|null $httpClient
     * @return void
     */
    public static function setHttpClient(ClientInterface $httpClient = null)
    {
        self::$httpClient = $httpClient;
        Util::logDebug('HttpClient set to \'' . get_class(self::$httpClient) . '\'');
    }

    /**
     * Get the HTTP client to use for sending the message. If one is not set, a default client is created.
     *
     * @return StreamClient|CurlClient|ClientInterface|null  The HTTP client
     */
    public static function getHttpClient()
    {
        if (!self::$httpClient) {
            if (function_exists('curl_init')) {
                self::$httpClient = new CurlClient();
            } elseif (ini_get('allow_url_fopen')) {
                self::$httpClient = new StreamClient();
            }
            if (self::$httpClient) {
                Util::logDebug('HttpClient set to \'' . get_class(self::$httpClient) . '\'');
            }
        }

        return self::$httpClient;
    }

    /**
     * Send the request to the target URL.
     *
     * @return bool    True if the request was successful
     */
    public function send(): bool
    {
        $client = self::getHttpClient();
        $this->relativeLinks = array();
        if (empty($client)) {
            $this->ok = false;
            $message = 'No HTTP client interface is available';
            $this->error = $message;
            Util::logError($message, true);
        } elseif (empty($this->url)) {
            $this->ok = false;
            $message = 'No URL provided for HTTP request';
            $this->error = $message;
            Util::logError($message, true);
        } else {
            $this->ok = $client->send($this);
            $this->parseRelativeLinks();
            if (Util::$logLevel > Util::LOGLEVEL_NONE) {
                $message = "Http\\HttpMessage->send {$this->method} request to '{$this->url}'";
                if (!empty($this->requestHeaders)) {
                    $message .= "\n{$this->requestHeaders}";
                }
                if (!empty($this->request)) {
                    $message .= "\n\n{$this->request}";
                }
                $message .= "\nResponse:";
                if (!empty($this->responseHeaders)) {
                    $message .= "\n{$this->responseHeaders}";
                }
                if (!empty($this->response)) {
                    $message .= "\n\n{$this->response}";
                }
                if ($this->ok) {
                    Util::logInfo($message);
                } else {
                    if (!empty($this->error)) {
                        $message .= "\nError: {$this->error}";
                    }
                    Util::logError($message);
                }
            }
        }

        return $this->ok;
    }

    /**
     * Check whether a relative link of the specified type exists.
     * @param string $rel
     * @return bool  True if it exists
     */
    public function hasRelativeLink(string $rel): bool
    {
        return array_key_exists($rel, $this->relativeLinks);
    }

    /**
     * Get the URL from the relative link with the specified type.
     * @param string $rel
     * @return string|null  The URL associated with the relative link, null if it is not defined
     */
    public function getRelativeLink(string $rel): ?string
    {
        $url = null;
        if ($this->hasRelativeLink($rel)) {
            $url = $this->relativeLinks[$rel];
        }

        return $url;
    }

    /**
     * Get the relative links.
     *
     * @return array  Associative array of relative links
     */
    public function getRelativeLinks(): array
    {
        return $this->relativeLinks;
    }

    ###
    ###  PRIVATE METHOD
    ###

    /**
     * Parse the response headers for relative links.
     */
    private function parseRelativeLinks()
    {
        $matched = preg_match_all('/^(Link|link): *(.*)$/m', $this->responseHeaders, $matches);
        if ($matched) {
            for ($i = 0; $i < $matched; $i++) {
                $links = explode(',', $matches[2][$i]);
                foreach ($links as $link) {
                    if (preg_match('/^\<([^\>]+)\>; *rel=([^ ]+)$/', trim($link), $match)) {
                        $rel = strtolower(utf8_decode($match[2]));
                        if ((strpos($rel, '"') === 0) || (strpos($rel, '?') === 0)) {
                            $rel = substr($rel, 1, strlen($rel) - 2);
                        }
                        if ($rel === 'previous') {
                            $rel = 'prev';
                        }
                        $this->relativeLinks[$rel] = $match[1];
                    }
                }
            }
        }
    }
}
