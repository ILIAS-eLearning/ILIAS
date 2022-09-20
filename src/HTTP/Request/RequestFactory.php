<?php

namespace ILIAS\HTTP\Request;

use Psr\Http\Message\ServerRequestInterface;

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
     */
    public function create(): ServerRequestInterface;
}
