<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\HTTP;

use ILIAS\HTTP\Cookies\CookieJar;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Agent\AgentDetermination;
use ILIAS\HTTP\Duration\DurationFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;

/**
 * Class Services
 * @author              Fabian Schmid <fs@studer-raimann.ch>
 * @description         This class only implements Services for backport compatibility. This will be removed in a
 * future release of the Service class
 */
class Services implements GlobalHttpState
{
    protected GlobalHttpState $raw;
    protected ?WrapperFactory $wrapper = null;
    protected AgentDetermination $agent;

    /**
     * Services constructor.
     */
    public function __construct(
        RequestFactory $request_factory,
        ResponseFactory $response_factory,
        CookieJarFactory $cookie_jar,
        ResponseSenderStrategy $response_sender_strategy,
        DurationFactory $duration_factory,
    ) {
        $this->raw = new RawHTTPServices(
            $response_sender_strategy,
            $cookie_jar,
            $request_factory,
            $response_factory,
            $duration_factory
        );
        $this->agent = new AgentDetermination();
    }

    public function sender(): ResponseSenderStrategy
    {
        return $this->raw()->sender();
    }

    public function durations(): DurationFactory
    {
        return $this->raw->durations();
    }

    public function wrapper(): WrapperFactory
    {
        return $this->wrapper ?? $this->wrapper = new WrapperFactory($this->raw->request());
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
    public function request(): ServerRequestInterface
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

    public function close(): never
    {
        $this->raw()->close();
    }

    public function agent(): AgentDetermination
    {
        return $this->agent;
    }
}
