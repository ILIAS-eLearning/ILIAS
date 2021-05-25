<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomPostMessageGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 * @TODO    DELETE THIS
 */
class ilChatroomPollGUI extends ilChatroomGUIHandler
{
    /**
     * @inheritDoc
     */
    public function executeDefault($requestedMethod)
    {
        echo "{success: true}";
        exit;
    }
}
