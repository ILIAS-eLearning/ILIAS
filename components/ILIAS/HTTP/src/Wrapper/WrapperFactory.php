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

namespace ILIAS\HTTP\Wrapper;

use Psr\Http\Message\RequestInterface;

/**
 * Class WrapperFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class WrapperFactory
{
    /**
     * WrapperFactory constructor.
     */
    public function __construct(private RequestInterface $request)
    {
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
