<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiAbstractRequest
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
abstract class ilCmiXapiAbstractRequest
{
    /**
     * @var string
     */
    private $basicAuth;
    
    /**
     * @var boolean
     */
    public static $plugin = false;

    /**
     * ilCmiXapiAbstractRequest constructor.
     * @param string $basicAuth
     */
    public function __construct(string $basicAuth)
    {
        $this->basicAuth = $basicAuth;
    }
    
    /**
     * @param string $url
     * @return string
     */
    protected function sendRequest($url)
    {
        $client = new GuzzleHttp\Client();
        $req_opts = array(
            GuzzleHttp\RequestOptions::VERIFY => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::HTTP_ERRORS => false
        );
        ilObjCmiXapi::log()->debug($url);
        $request = new GuzzleHttp\Psr7\Request('GET', $url, [
            'Authorization' => $this->basicAuth,
            'X-Experience-API-Version' => '1.0.3'
        ]);
        try {
            $body = '';
            $promises = array();
            $promises['default'] = $client->sendAsync($request, $req_opts);
            $responses = GuzzleHttp\Promise\settle($promises)->wait();
            self::checkResponse($responses['default'],$body);
            return $body;
        }
        catch(Exception $e) {
            ilObjCmiXapi::log()->error($e->getMessage());
            throw new Exception("LRS Connection Problems");
        }
    }

    public static function checkResponse($response, &$body, $allowedStatus = [200, 204])
    {
        if ($response['state'] == 'fulfilled')
        {
            $status = $response['value']->getStatusCode();
            if (in_array($status,$allowedStatus))
            {
                $body = $response['value']->getBody();
                return true;
            }
            else
            {
                ilObjCmiXapi::log()->error("LRS error: " . $response['value']->getBody());
                return false;
            }
        }
        else {
            try
            {
                ilObjCmiXapi::log()->error("Connection error: " . $response['reason']->getMessage());
            }
            catch(Exception $e)
            {
                ilObjCmiXapi::log()->error('error:' . $e->getMessage());
            }
            return false;
        }
        return false;
    }

    public static function buildQuery(array $params, $encoding = PHP_QUERY_RFC3986)
    {
        if (!$params) {
            return '';
        }

        if ($encoding === false) {
            $encoder = function ($str) {
                return $str;
            };
        } elseif ($encoding === PHP_QUERY_RFC3986) {
            $encoder = 'rawurlencode';
        } elseif ($encoding === PHP_QUERY_RFC1738) {
            $encoder = 'urlencode';
        } else {
            throw new \InvalidArgumentException('Invalid type');
        }

        $qs = '';
        foreach ($params as $k => $v) {
            $k = $encoder($k);
            if (!is_array($v)) {
                $qs .= $k;
                if ($v !== null) {
                    $qs .= '=' . $encoder($v);
                }
                $qs .= '&';
            } else {
                foreach ($v as $vv) {
                    $qs .= $k;
                    if ($vv !== null) {
                        $qs .= '=' . $encoder($vv);
                    }
                    $qs .= '&';
                }
            }
        }
        return $qs ? (string) substr($qs, 0, -1) : '';
    }
}
