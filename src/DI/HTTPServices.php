<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Provides an interface to the ILIAS HTTP services.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class HTTPServices implements GlobalHttpState
{

    /**
     * @var ResponseSenderStrategy
     */
    private $sender;
    /**
     * @var CookieJarFactory $cookieJarFactory
     */
    private $cookieJarFactory;
    /**
     * @var RequestFactory $requestFactory
     */
    private $requestFactory;
    /**
     * @var ResponseFactory $responseFactory
     */
    private $responseFactory;
    /**
     * @var ServerRequestInterface $request
     */
    private $request;
    /**
     * @var ResponseInterface $response
     */
    private $response;


    /**
     * HTTPServices constructor.
     *
     * @param ResponseSenderStrategy $senderStrategy   A response sender strategy.
     * @param CookieJarFactory       $cookieJarFactory Cookie Jar implementation.
     * @param RequestFactory         $requestFactory
     * @param ResponseFactory        $responseFactory
     */
    public function __construct(ResponseSenderStrategy $senderStrategy, CookieJarFactory $cookieJarFactory, RequestFactory $requestFactory, ResponseFactory $responseFactory)
    {
        $this->sender = $senderStrategy;
        $this->cookieJarFactory = $cookieJarFactory;

        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }


    /**
     * Creates a new cookie jar from the current known request.
     *
     * @return \ILIAS\HTTP\Cookies\CookieJar
     */
    public function cookieJar()
    {
        return $this->cookieJarFactory->fromResponse($this->response());
    }


    /**
     * @inheritDoc
     */
    public function request()
    {
        if ($this->request === null) {
            $this->request = $this->requestFactory->create();
        }

        return $this->request;
    }


    /**
     * @inheritDoc
     */
    public function response()
    {
        if ($this->response === null) {
            $this->response = $this->responseFactory->create();
        }

        return $this->response;
    }


    /**
     * @inheritDoc
     */
    public function saveRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }


    /**
     * @inheritDoc
     */
    public function saveResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }


    /**
     * @inheritDoc
     */
    public function sendResponse()
    {
        $this->sender->sendResponse($this->response());
    }
}
