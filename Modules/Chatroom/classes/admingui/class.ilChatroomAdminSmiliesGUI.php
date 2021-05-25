<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmiliesGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomAdminSmiliesGUI extends ilChatroomGUIHandler
{
    /**
     * @inheritDoc
     */
    public function executeDefault($requestedMethod)
    {
        global $DIC;

        $this->gui->switchToVisibleMode();
        $DIC->ui()->mainTemplate()->setVariable('ADM_CONTENT', '');
    }
}
