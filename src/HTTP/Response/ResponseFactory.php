<?php

namespace ILIAS\HTTP\Response;

use Psr\Http\Message\ResponseInterface;

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
     */
    public function create() : ResponseInterface;
}
