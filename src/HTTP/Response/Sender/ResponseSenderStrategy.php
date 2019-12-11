<?php

namespace ILIAS\HTTP\Response\Sender;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseSenderStrategy
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
interface ResponseSenderStrategy
{

    /**
     * Sends the rendered response to the client.
     *
     * @param ResponseInterface $response The response which should be send to the client.
     *
     * @return void
     * @throws ResponseSendingException Thrown if the response was already sent to the client.
     */
    public function sendResponse(ResponseInterface $response);
}
