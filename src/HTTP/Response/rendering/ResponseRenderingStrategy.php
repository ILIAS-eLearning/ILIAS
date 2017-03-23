<?php
namespace ILIAS\HTTP\Response\rendering;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseRenderingStrategy
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
interface ResponseRenderingStrategy {

	/**
	 * Sends the rendered response to the client.
	 *
	 * @param ResponseInterface $response The response which should be send to the client.
	 *
	 * @return void
	 * @throws RenderingException Thrown if the response was already sent to the client.
	 */
	public function renderResponse(ResponseInterface $response);
}