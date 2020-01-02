<?php

namespace ILIAS\HTTP\Request;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestFactory
 *
 * The RequestFactory produces PSR-7
 * compliant ServerRequest instances.
 *
 * @package ILIAS\HTTP\Request
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 */
interface RequestFactory
{

    /**
     * Creates a new ServerRequest object with the help of the underlying library.
     *
     * @return ServerRequestInterface
     */
    public function create();
}
