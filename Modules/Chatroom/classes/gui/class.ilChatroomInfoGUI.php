<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomInfoGUI
 * Provides methods to prepare and display the info task.
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInfoGUI extends ilChatroomGUIHandler
{
    /**
     * @inheritDoc
     */
    public function executeDefault($requestedMethod)
    {
        $this->redirectIfNoPermission('visible');

        $this->gui->switchToVisibleMode();

        $info = $this->createInfoScreenGUI($this->gui);

        $info->enablePrivateNotes();

        if (ilChatroom::checkUserPermissions("read", (int) $_GET["ref_id"], false)) {
            $info->enableNews();
        }

        $info->addMetaDataSections(
            $this->gui->object->getId(),
            0,
            $this->gui->object->getType()
        );
        if (!$method) {
            $this->ilCtrl->setCmd('showSummary');
        } else {
            $this->ilCtrl->setCmd($method);
        }
        $this->ilCtrl->forwardCommand($info);
    }

    /**
     * @param ilChatroomObjectGui $gui
     * @return ilInfoScreenGUI
     */
    protected function createInfoScreenGUI($gui)
    {
        return new ilInfoScreenGUI($gui);
    }
}
