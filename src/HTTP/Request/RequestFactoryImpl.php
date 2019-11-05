<?php

namespace ILIAS\HTTP\Request;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestFactoryImpl
 *
 * This class creates new psr-7 compliant ServerRequests
 * and decouples the used library from ILIAS components.
 *
 * The currently used psr-7 implementation is created and published by guzzle under the MIT license.
 * source: https://github.com/guzzle/psr7
 *
 * @package ILIAS\HTTP\Request
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 */
class RequestFactoryImpl implements RequestFactory
{

    /**
     * @inheritdoc
     */
    public function create() : ServerRequestInterface
    {
        return ServerRequest::fromGlobals();
    }
}