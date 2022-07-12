<?php

namespace ILIAS\HTTP\Response\Sender;

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
 * Class NullResponseSenderStrategy
 *
 * Noop implementation for testing purposes.
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Response\rendering
 */
class NullResponseSenderStrategy implements ResponseSenderStrategy
{

    /**
     * Noop.
     *
     * @param ResponseInterface $response Ignored.
     */
    public function sendResponse(ResponseInterface $response) : void
    {
        /** @noRector */
        // nothing to do here
    }
}
