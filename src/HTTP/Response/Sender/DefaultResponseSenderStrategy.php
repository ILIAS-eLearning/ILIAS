<?php

namespace ILIAS\HTTP\Response\Sender;

use Psr\Http\Message\ResponseInterface;

/**
 * Class DefaultResponseSenderStrategy
 *
 * The default response sender strategy rewinds the current body
 * stream and sends the entire stream out to the client.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class DefaultResponseSenderStrategy implements ResponseSenderStrategy
{

    /**
     * Sends the rendered response to the client.
     *
     * @param ResponseInterface $response The response which should be send to the client.
     *
     * @return void
     * @throws ResponseSendingException Thrown if the response was already sent to the client.
     */
    public function sendResponse(ResponseInterface $response)
    {
        //check if the request is already send
        if (headers_sent()) {
            throw new ResponseSendingException("Response was already sent.");
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

            //free up resources
            fclose($resource);
        }

        //check if the body was successfully send to the client
        if ($sendStatus === false) {
            throw new ResponseSendingException("Could not send body content to client.");
        }
    }
}
