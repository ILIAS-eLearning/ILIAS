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

namespace ILIAS\HTTP\Response\Sender;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface ResponseSenderStrategy
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
interface ResponseSenderStrategy
{
    /**
     * Sends the rendered response to the client.
     *
     * @param ResponseInterface $response The response which should be send to the client.
     *
     * @throws ResponseSendingException Thrown if the response was already sent to the client.
     */
    public function sendResponse(ResponseInterface $response): void;
}
