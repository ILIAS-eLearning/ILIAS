<?php

namespace ILIAS\HTTP\Response;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseFactory
 *
 * This class creates new psr-7 compliant Response
 * and decouples the used library from ILIAS components.
 *
 * The currently used psr-7 implementation is created and published by guzzle under the MIT license.
 * source: https://github.com/guzzle/psr7
 *
 * @package ILIAS\HTTP\Response
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 */
class ResponseFactory {

	/**
	 * Creates a new response with the help of the underlying library.
	 *
	 * @return ResponseInterface
	 */
	public static function create()
	{
		return new Response();
	}
}