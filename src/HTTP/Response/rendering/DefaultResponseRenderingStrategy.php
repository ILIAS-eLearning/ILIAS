<?php

namespace ILIAS\HTTP\Response\rendering;

use Psr\Http\Message\ResponseInterface;

/**
 * Class DefaultResponseRenderingStrategy
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class DefaultResponseRenderingStrategy implements ResponseRenderingStrategy {

	/**
	 * Sends the rendered response to the client.
	 *
	 * @param ResponseInterface $response The response which should be send to the client.
	 * @return void
	 * @throws RenderingException Thrown if the response was already sent to the client.
	 */
	public function renderResponse(ResponseInterface $response)
	{
		//check if the request is already send
		if (headers_sent()) {
			throw new RenderingException("Response was already sent.");
		}

		//set status code
		http_response_code($response->getStatusCode());

		//render all headers
		foreach ($response->getHeaders() as $key => $header) {
			header("$key: " . $response->getHeaderLine($key));
		}

		//rewind body stream
		$response->getBody()->rewind();

		//detach psr-7 stream from resource
		$resource = $response->getBody()->detach();

		$sendStatus = false;

		if (is_resource($resource)) {
			set_time_limit(0);
			$sendStatus = fpassthru($resource);
		}

		//free up resources
		$response->getBody()->close();

		//check if the body was successfully send to the client
		if ($sendStatus === false) {
			throw new RenderingException("Could not send body content to client.");
		}
	}
}