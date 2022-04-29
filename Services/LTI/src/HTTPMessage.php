<?php

namespace ILIAS\LTI;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/
class HTTPMessage
{
    public mixed $responseJson; // TODO PHP8 Review: Type `mixed` is not supported!

    /**
 * True if message was sent successfully.
 *
 * @var boolean $ok
 */
    public bool $ok = false;

    /**
     * Request body.
     *
     * @var mixed|null|string $request
     */
    public mixed $request = null; // TODO PHP8 Review: Type `mixed` is not supported!

    /**
     * Request headers.
     *
     * @var bool|string|string[] $requestHeaders
     */
    // TODO PHP8 Review: Union Types are not supported by PHP 7.4!
    public $requestHeaders = '';

    /**
     * Response body.
     *
     * @var string|null $response
     */
    public ?string $response = null;

    /**
     * Response headers.
     */
    public string $responseHeaders = '';

    /**
     * Status of response (0 if undetermined).
     */
    public int $status = 0;

    /**
     * Error message
     */
    public string $error = '';

    /**
                     * Request URL.
                     */
    private ?string $url = null;

    /**
                     * Request method.
                     */
    private ?string $method = null;

    /**
     * Class constructor.
     * @param string      $url    URL to send request to
     * @param string      $method Request method to use (optional, default is GET)
     * @param mixed       $params Associative array of parameter values to be passed or message body (optional, default is none)
     * @param string|null $header Values to include in the request header (optional, default is none)
     */
    public function __construct(string $url, string $method = 'GET', ?array $params = null, string $header = null)
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
     * Send the request to the target URL.
     *
     * @return boolean True if the request was successful
     */
    public function send() : bool
    {
        $this->ok = false;
        // Try using curl if available
        if (function_exists('curl_init')) {
            $resp = '';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            if (!empty($this->requestHeaders)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
            } else {
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
            if ($this->method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request);
            } elseif ($this->method !== 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
                if (!is_null($this->request)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request);
                }
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            //beginn patch ILIAS nor for Trunk!!!!
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //end patch ILIAS
            #curl_setopt($ch, CURLOPT_SSLVERSION,3);
            $chResp = curl_exec($ch);
            \ilLoggerFactory::getLogger('ltis')->dump(curl_getinfo($ch), \ilLogLevel::DEBUG);
            \ilLoggerFactory::getLogger('ltis')->dump(curl_error($ch), \ilLogLevel::DEBUG);
            $this->ok = $chResp !== false;
            if ($this->ok) {
                $chResp = str_replace("\r\n", "\n", $chResp);
                $chRespSplit = explode("\n\n", $chResp, 2);
                if ((count($chRespSplit) > 1) && (substr($chRespSplit[1], 0, 5) === 'HTTP/')) {
                    $chRespSplit = explode("\n\n", $chRespSplit[1], 2);
                }
                $this->responseHeaders = $chRespSplit[0];
                $resp = $chRespSplit[1];
                $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $this->ok = $this->status < 400;
                if (!$this->ok) {
                    $this->error = curl_error($ch);
                }
            }
            $this->requestHeaders = str_replace("\r\n", "\n", curl_getinfo($ch, CURLINFO_HEADER_OUT));
            curl_close($ch);
            $this->response = $resp;
        } else {
            // Try using fopen if curl was not available
            $opts = array('method' => $this->method,
                          'content' => $this->request
                         );
            if (!empty($this->requestHeaders)) {
                $opts['header'] = $this->requestHeaders;
            }
            try {
                $ctx = stream_context_create(array('http' => $opts));
                $fp = @fopen($this->url, 'rb', false, $ctx);
                if ($fp) {
                    $resp = @stream_get_contents($fp);
                    $this->ok = $resp !== false;
                }
            } catch (\Exception $e) {
                $this->ok = false;
            }
        }

        return $this->ok;
    }
}
