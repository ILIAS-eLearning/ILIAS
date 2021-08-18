<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomPostMessageGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomPollGUI extends ilChatroomGUIHandler
{
    public function executeDefault(string $requestedMethod) : void
    {
        $this->sendResponse(['success' => true]);
    }
}
