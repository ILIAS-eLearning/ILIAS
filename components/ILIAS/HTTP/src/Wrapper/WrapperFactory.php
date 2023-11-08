<?php

declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use Psr\Http\Message\RequestInterface;

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
 * Class WrapperFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class WrapperFactory
{
    private RequestInterface $request;

    /**
     * WrapperFactory constructor.
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function query(): ArrayBasedRequestWrapper
    {
        return new ArrayBasedRequestWrapper($this->request->getQueryParams());
    }

    public function post(): ArrayBasedRequestWrapper
    {
        return new ArrayBasedRequestWrapper($this->request->getParsedBody());
    }

    public function cookie(): ArrayBasedRequestWrapper
    {
        return new ArrayBasedRequestWrapper($this->request->getCookieParams());
    }
}
