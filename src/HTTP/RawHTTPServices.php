<?php

namespace ILIAS\HTTP;

use ILIAS\HTTP\Cookies\CookieJar;
use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Provides an interface to the ILIAS HTTP services.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class RawHTTPServices implements GlobalHttpState
{
    private \ILIAS\HTTP\Response\Sender\ResponseSenderStrategy $sender;
    private \ILIAS\HTTP\Cookies\CookieJarFactory $cookieJarFactory;
    private \ILIAS\HTTP\Request\RequestFactory $requestFactory;
    private \ILIAS\HTTP\Response\ResponseFactory $responseFactory;
    private ?\Psr\Http\Message\ServerRequestInterface $request = null;
    private ?\Psr\Http\Message\ResponseInterface $response = null;


    /**
     * RawHTTPServices constructor.
     *
     * @param ResponseSenderStrategy $senderStrategy   A response sender strategy.
     * @param CookieJarFactory       $cookieJarFactory Cookie Jar implementation.
     */
    public function __construct(ResponseSenderStrategy $senderStrategy, CookieJarFactory $cookieJarFactory, RequestFactory $requestFactory, ResponseFactory $responseFactory)
    {
        $this->sender = $senderStrategy;
        $this->cookieJarFactory = $cookieJarFactory;

        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
    }


    public function wrapper(): WrapperFactory
    {
        return new WrapperFactory($this->request());
    }


    /**
     * @inheritDoc
     */
    public function cookieJar(): CookieJar
    {
        return $this->cookieJarFactory->fromResponse($this->response());
    }


    /**
     * @inheritDoc
     */
    public function request(): \Psr\Http\Message\RequestInterface
    {
        if ($this->request === null) {
            $this->request = $this->requestFactory->create();
        }

        return $this->request;
    }


    /**
     * @inheritDoc
     */
    public function response(): ResponseInterface
    {
        if ($this->response === null) {
            $this->response = $this->responseFactory->create();
        }

        return $this->response;
    }


    /**
     * @inheritDoc
     */
    public function saveRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }


    /**
     * @inheritDoc
     */
    public function saveResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }


    /**
     * @inheritDoc
     */
    public function sendResponse(): void
    {
        $this->sender->sendResponse($this->response());
    }


    public function close(): void
    {
        exit;
    }
}
