<?php declare(strict_types=1);

namespace ILIAS\HTTP;

use ILIAS\HTTP\Cookies\CookieJar;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;

/**
 * Class Services
 * @author              Fabian Schmid <fs@studer-raimann.ch>
 * @description         This class only implements Services for backport compatibility. This will be removed in a
 * future release of the Service class
 */
class Services implements GlobalHttpState
{
    /**
     * @var RawHTTPServices
     */
    protected $raw;
    /**
     * @var WrapperFactory
     */
    protected $wrapper;

    /**
     * Services constructor.
     * @param Container $dic
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
    }

    /**
     * @return WrapperFactory
     */
    public function wrapper() : WrapperFactory
    {
        return $this->wrapper;
    }

    /**
     * @deprecated Please use $this->wrapper()
     * @see        Services::wrapper();
     */
    public function raw() : GlobalHttpState
    {
        return $this->raw;
    }

    /**
     * @deprecated Please use $this->wrapper() in most cases.
     * @see        Services::wrapper();
     * @inheritDoc
     */
    public function request() : RequestInterface
    {
        return $this->raw()->request();
    }

    /**
     * @deprecated Please use $this->wrapper() in most cases.
     * @see        Services::wrapper();
     * @inheritDoc
     */
    public function response() : ResponseInterface
    {
        return $this->raw()->response();
    }

    /**
     * @deprecated Please use $this->wrapper() in most cases.
     * @see        Services::wrapper();
     * @inheritDoc
     */
    public function cookieJar() : CookieJar
    {
        return $this->raw()->cookieJar();
    }

    /**
     * @inheritDoc
     */
    public function saveRequest(ServerRequestInterface $request) : void
    {
        $this->raw()->saveRequest($request);
    }

    /**
     * @inheritDoc
     */
    public function saveResponse(ResponseInterface $response) : void
    {
        $this->raw()->saveResponse($response);
    }

    /**
     * @inheritDoc
     */
    public function sendResponse() : void
    {
        $this->raw()->sendResponse();
    }

    public function close() : void
    {
        $this->raw()->close();
    }

}
