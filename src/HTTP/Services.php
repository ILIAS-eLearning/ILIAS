<?php

declare(strict_types=1);

namespace ILIAS\HTTP;

use ILIAS\HTTP\Cookies\CookieJar;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;
use ILIAS\HTTP\Agent\AgentDetermination;

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
 * Class Services
 * @author              Fabian Schmid <fs@studer-raimann.ch>
 * @description         This class only implements Services for backport compatibility. This will be removed in a
 * future release of the Service class
 */
class Services implements GlobalHttpState
{
    protected GlobalHttpState $raw;
    protected WrapperFactory $wrapper;
    protected AgentDetermination $agent;

    /**
     * Services constructor.
     */
    public function __construct(Container $dic)
    {
        $this->raw = new RawHTTPServices(
            $dic['http.response_sender_strategy'],
            $dic['http.cookie_jar_factory'],
            $dic['http.request_factory'],
            $dic['http.response_factory']
        );
        $this->wrapper = new WrapperFactory($this->raw->request());
        $this->agent = new AgentDetermination();
    }

    public function wrapper(): WrapperFactory
    {
        return $this->wrapper;
    }

    /**
     * @deprecated Please use $this->wrapper()
     * @see        Services::wrapper();
     */
    public function raw(): RawHTTPServices
    {
        return $this->raw;
    }

    /**
     * @deprecated Please use $this->wrapper() in most cases.
     * @see        Services::wrapper();
     * @inheritDoc
     */
    public function request(): RequestInterface
    {
        return $this->raw()->request();
    }

    /**
     * @deprecated Please use $this->wrapper() in most cases.
     * @see        Services::wrapper();
     * @inheritDoc
     */
    public function response(): ResponseInterface
    {
        return $this->raw()->response();
    }

    /**
     * @deprecated Please use $this->wrapper() in most cases.
     * @see        Services::wrapper();
     * @inheritDoc
     */
    public function cookieJar(): CookieJar
    {
        return $this->raw()->cookieJar();
    }

    /**
     * @inheritDoc
     */
    public function saveRequest(ServerRequestInterface $request): void
    {
        $this->raw()->saveRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function saveResponse(ResponseInterface $response): void
    {
        $this->raw()->saveResponse($response);
    }

    /**
     * @inheritDoc
     */
    public function sendResponse(): void
    {
        $this->raw()->sendResponse();
    }

    public function close(): void
    {
        $this->raw()->close();
    }

    public function agent(): AgentDetermination
    {
        return $this->agent;
    }
}
