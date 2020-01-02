<?php

namespace ILIAS\HTTP\Response\Sender;

use Psr\Http\Message\ResponseInterface;

/**
 * Class NullResponseSenderStrategy
 *
 * Noop implementation for testing purposes.
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Response\rendering
 */
class NullResponseSenderStrategy implements ResponseSenderStrategy
{

    /**
     * Noop.
     *
     * @param ResponseInterface $response Ignored.
     *
     * @return void
     */
    public function sendResponse(ResponseInterface $response)
    {
        //noop
        return;
    }
}
