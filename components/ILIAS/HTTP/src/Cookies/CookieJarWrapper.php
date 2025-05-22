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

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CookieJarWrapper
 *
 * Wrapper class for the FigCookies SetCookies class.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
class CookieJarWrapper implements CookieJar
{
    /**
     * CookieJarWrapper constructor.
     */
    public function __construct(private SetCookies $cookies)
    {
    }


    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return $this->cookies->has($name);
    }


    /**
     * @inheritDoc
     */
    public function get(string $name): ?Cookie
    {
        $cookie = $this->cookies->get($name);

        return (is_null($cookie)) ? null : new CookieWrapper($cookie);
    }


    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $wrappedCookies = [];
        foreach ($this->cookies->getAll() as $cookie) {
            $wrappedCookies[] = new CookieWrapper($cookie);
        }

        return $wrappedCookies;
    }


    /**
     * @inheritDoc
     */
    public function with(Cookie $setCookie): CookieJar
    {
        /**
         * @var CookieWrapper $wrapper
         */
        $wrapper = $setCookie;
        $internalCookie = $wrapper->getImplementation();

        $clone = clone $this;
        $clone->cookies = $this->cookies->with($internalCookie);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function without(string $name): CookieJar
    {
        $clone = clone $this;
        $clone->cookies = $this->cookies->without($name);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function renderIntoResponseHeader(ResponseInterface $response): ResponseInterface
    {
        return $this->cookies->renderIntoSetCookieHeader($response);
    }
}
