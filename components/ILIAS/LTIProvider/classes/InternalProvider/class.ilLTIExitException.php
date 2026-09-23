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

/**
 * Thrown by the LTI library instead of terminating the request on its own.
 *
 * The library is designed to be the only thing handling an LTI request, so it ends the request
 * itself once the message has been processed. ILIAS still has to authenticate the user and send
 * them to the requested object afterwards, so it asks the library to throw this exception rather
 * than to exit, and regains control in ilLTITool::handleRequest().
 */
class ilLTIExitException extends ilException
{
    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
