<?php

namespace ILIAS\HTTP\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseFactory
 *
 * The ResponseFactory produces PSR-7
 * compliant Response instances.
 *
 * @package ILIAS\HTTP\Response
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 */
interface ResponseFactory
{

    /**
     * Creates a new response with the help of the underlying library.
     *
     * @return ResponseInterface
     */
    public function create();
}
