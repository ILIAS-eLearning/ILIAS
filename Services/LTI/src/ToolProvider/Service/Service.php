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


namespace ILIAS\LTI\ToolProvider\Service;

use ILIAS\LTI\ToolProvider\AccessToken;
use ILIAS\LTI\ToolProvider\Platform;
use ILIAS\LTI\ToolProvider\Http\HTTPMessage;
use ILIAS\LTI\ToolProvider\Util;


/**
 * Class to implement a service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Service
{

    /**
     * Whether service request should be sent unsigned.
     *
     * @var bool $unsigned
     */
    public bool $unsigned = false;

    /**
     * Service endpoint.
     *
     * @var string $endpoint
     */
    protected ?string $endpoint = null;

    /**
     * Service access scope.
     *
     * @var string $scope
     */
    protected ?string $scope = null;

    /**
     * Media type of message body.
     *
     * @var string $mediaType
     */
    protected ?string $mediaType = null;

    /**
     * Platform for this service request.
     *
     * @var Platform $platform
     */
    private ?Platform $platform = null;

    /**
     * HttpMessage object for last service request.
     *
     * @var HttpMessage|null $http
     */
    private ?HTTPMessage $http = null;

    /**
     * Class constructor.
     * @param Platform $platform Platform object for this service request
     * @param string   $endpoint Service endpoint
     */
    public function __construct(Platform $platform, string $endpoint)
    {
        $this->platform = $platform;
        $this->endpoint = $endpoint;
    }

//    /**
//     * Get tool consumer.
//     *
//     * @deprecated Use getPlatform() instead
//     * @see Service::getPlatform()
//     *
//     * @return ToolConsumer Consumer for this service
//     */
//    public function getConsumer()
//    {
//        Util::logDebug('Method ceLTIc\LTI\Service::getConsumer() has been deprecated; please use ceLTIc\LTI\Service::getPlatform() instead.',
//            true);
//        return $this->getPlatform();
//    }

    /**
     * Get platform.
     *
     * @return Platform  Platform for this service
     */
    public function getPlatform() : ?Platform
    {
        return $this->platform;
    }

    /**
     * Get access scope.
     *
     * @return string Access scope
     */
    public function getScope() : ?string
    {
        return $this->scope;
    }

    /**
     * Send a service request.
     * @param string      $method     The action type constant (optional, default is GET)
     * @param array       $parameters Query parameters to add to endpoint (optional, default is none)
     * @param string|null $body       Body of request (optional, default is null)
     * @return HttpMessage HTTP object containing request and response details
     */
    public function send(string $method, array $parameters = array(), string $body = null) : ?HTTPMessage
    {
        $url = $this->endpoint;
        if (!empty($parameters)) {
            if (strpos($url, '?') === false) {
                $sep = '?';
            } else {
                $sep = '&';
            }
            foreach ($parameters as $name => $value) {
                $url .= $sep . urlencode($name) . '=' . urlencode($value);
                $sep = '&';
            }
        }
        $header = null;
        $retry = !$this->platform->useOAuth1();
        $newToken = false;
        $retried = false;
        do {
            if (!$this->unsigned) {
                $accessToken = $this->platform->getAccessToken();
                if (!$this->platform->useOAuth1()) {
                    if (empty($accessToken)) {
                        $accessToken = new AccessToken($this->platform);
                        $this->platform->setAccessToken($accessToken);
                    }
                    if (!$accessToken->hasScope($this->scope)) {
                        $accessToken->get($this->scope);
                        $newToken = true;
                        if (!$accessToken->hasScope($this->scope)) {  // Try obtaining a token for just this scope
                            $accessToken->expires = time();
                            $accessToken->get($this->scope, true);
                            $retried = true;
                            if (!$accessToken->hasScope($this->scope)) {
                                if (empty($this->http)) {
                                    $this->http = new HttpMessage($url);
                                    $this->http->error = "Unable to obtain an access token for scope: {$this->scope}";
                                }
                                break;
                            }
                        }
                    }
                }
                $header = $this->platform->signServiceRequest($url, $method, $this->mediaType, $body);
            }
            // Connect to platform and parse JSON response
            $this->http = new HttpMessage($url, $method, $body, $header);
            if ($this->http->send() && !empty($this->http->response)) {
                $this->http->responseJson = json_decode($this->http->response);
                $this->http->ok = !is_null($this->http->responseJson);
            }
            $retry = $retry && !$retried && !$this->http->ok;
            if ($retry) {
                if (!$newToken) {  // Invalidate existing token to force a new one to be obtained
                    $accessToken->expires = time();
                    $newToken = true;
                } elseif (count($accessToken->scopes) !== 1) {  // Try obtaining a token for just this scope
                    $accessToken->expires = time();
                    $accessToken->get($this->scope, true);
                    $retried = true;
                } else {
                    $retry = false;
                }
            }
        } while ($retry);

        return $this->http;
    }

    /**
     * Get HttpMessage object for last request.
     *
     * @return HttpMessage HTTP object containing request and response details
     */
    public function getHttpMessage() : ?HTTPMessage
    {
        return $this->http;
    }

    ###
    ###  PROTECTED METHODS
    ###

    /**
     * Parse the JSON for context references.
     * @param object $contexts JSON contexts
     * @param array  $arr      Array to be parsed
     * @return array Parsed array
     */
    protected function parseContextsInArray(object $contexts, array $arr) : array
    {
        if (is_array($contexts)) {
            $contextdefs = array();
            foreach ($contexts as $context) {
                if (is_object($context)) {
                    $contextdefs = array_merge(get_object_vars($context), $contexts);
                }
            }
            $parsed = array();
            foreach ($arr as $key => $value) {
                $parts = explode(':', $value, 2);
                if (count($parts) > 1) {
                    if (array_key_exists($parts[0], $contextdefs)) {
                        $parsed[$key] = $contextdefs[$parts[0]] . $parts[1];
                        break;
                    }
                }
                $parsed[$key] = $value;
            }
        } else {
            $parsed = $arr;
        }

        return $parsed;
    }
}
