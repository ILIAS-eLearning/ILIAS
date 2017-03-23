<?php

namespace ILIAS\HTTP\Request;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestFactory
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
class RequestFactory {

	/**
	 * Creates a new ServerRequest object with the help of the underlying library.
	 *
	 * @return ServerRequestInterface
	 */
	public static function create()
	{
		return ServerRequest::fromGlobals();
	}
}