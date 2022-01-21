<?php declare(strict_types=1);
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
    protected function createInfoScreenGUI(ilChatroomObjectGUI $gui) : ilInfoScreenGUI
    {
        return new ilInfoScreenGUI($gui);
    }

    public function executeDefault(string $requestedMethod) : void
    {
        $this->redirectIfNoPermission('visible');

        $this->gui->switchToVisibleMode();

        $info = $this->createInfoScreenGUI($this->gui);

        $info->enablePrivateNotes();
        
        $refId = $this->getRequestValue('ref_id', $this->refinery->kindlyTo()->int());
        if (ilChatroom::checkUserPermissions('read', $refId, false)) {
            $info->enableNews();
        }

        $info->addMetaDataSections(
            $this->gui->object->getId(),
            0,
            $this->gui->object->getType()
        );
        if ($requestedMethod === '') {
            $this->ilCtrl->setCmd('showSummary');
        } else {
            $this->ilCtrl->setCmd($requestedMethod);
        }
        $this->ilCtrl->forwardCommand($info);
    }
}
