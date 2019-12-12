<?php declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use Psr\Http\Message\RequestInterface;

/**
 * Class WrapperFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class WrapperFactory
{

    /**
     * @var RequestInterface
     */
    private $request;


    /**
     * WrapperFactory constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }


    public function query() : RequestWrapper
    {
        return new ArrayBasedRequestWrapper($this->request->getQueryParams());
    }


    public function post() : RequestWrapper
    {
        return new ArrayBasedRequestWrapper($this->request->getParsedBody());
    }


    public function cookie() : RequestWrapper
    {
        return new ArrayBasedRequestWrapper($this->request->getCookieParams());
    }
}
