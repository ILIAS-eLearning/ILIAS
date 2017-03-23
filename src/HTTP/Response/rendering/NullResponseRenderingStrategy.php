<?php

namespace ILIAS\HTTP\Response\rendering;

use Psr\Http\Message\ResponseInterface;

/**
 * Class NullResponseRenderingStrategy
 *
 * Noop implementation for testing purposes.
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Response\rendering
 */
class NullResponseRenderingStrategy implements ResponseRenderingStrategy {

	/**
	 * Noop.
	 *
	 * @param ResponseInterface $response Ignored.
	 * @return void
	 */
	public function renderResponse(ResponseInterface $response)
	{
		//noop
		return;
	}
}