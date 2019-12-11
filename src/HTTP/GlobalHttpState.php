<?php
/* Copyright (c) 2016 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\HTTP;

use ILIAS\HTTP\Cookies\CookieJar;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface GlobalHttpState
 *
 * The GlobalHttpState expose functions to access the cookies,
 * request and response. In addition there are functions to save the
 * request and response back into the service. The save functionality is only provided due to
 * the current architectural state of ILIAS.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
interface GlobalHttpState
{

    /**
     * Returns the current psr-7 server request.
     *
     * @return ServerRequestInterface
     */
    public function request();


    /**
     * Returns the current psr-7 response.
     *
     * @return ResponseInterface
     */
    public function response();


    /**
     * Returns a cookie jar which has all cookies known by the ILIAS response.
     * Make sure to call the saveResponse method when the cookies are rendered into the response
     * object.
     *
     * @return CookieJar
     */
    public function cookieJar();


    /**
     * Saves the given request for further use.
     * The request should only be saved if absolutely necessary.
     * There is a possibility that the request can't be saved back in the near future.
     *
     * @param ServerRequestInterface $request The server request which should be saved.
     *
     * @return void
     */
    public function saveRequest(ServerRequestInterface $request);


    /**
     * Saves the given response for further use.
     *
     * @param ResponseInterface $response The response which should be saved.
     *
     * @return void
     */
    public function saveResponse(ResponseInterface $response);


    /**
     * Render the current response hold by ILIAS.
     *
     * @throws ResponseSendingException Each subsequent call will throw this exception.
     * @return void
     */
    public function sendResponse();
}
