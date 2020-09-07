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
     * Prepares and displays the info screen.
     * @param string $method
     * @throws ilCtrlException
     */
    public function executeDefault($method)
    {
        include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();

        if (!ilChatroom::checkUserPermissions("visible", $this->gui->ref_id, false)) {
            $this->gui->ilias->raiseError(
                $this->ilLng->txt("msg_no_perm_read"),
                $this->ilias->error_obj->MESSAGE
            );
        }

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
        require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
        return new ilInfoScreenGUI($gui);
    }
}
