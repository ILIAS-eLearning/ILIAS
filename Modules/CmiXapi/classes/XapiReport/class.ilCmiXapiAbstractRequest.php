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
        $request = new GuzzleHttp\Psr7\Request('GET', $url, [
            'Authorization' => $this->basicAuth,
            'X-Experience-API-Version' => '1.0.0'
        ]);
        try {
            $response = $client->sendAsync($request)->wait();
            return (string) $response->getBody();
        }
        catch(Exception $e) {
            throw new Exception("LRS Connection Problems");
        }
    }
}
