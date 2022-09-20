<?php

declare(strict_types=1);

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

/**
 * Class ilChatroomInfoGUI
 * Provides methods to prepare and display the info task.
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomInfoGUI extends ilChatroomGUIHandler
{
    protected function createInfoScreenGUI(ilChatroomObjectGUI $gui): ilInfoScreenGUI
    {
        return new ilInfoScreenGUI($gui);
    }

    public function executeDefault(string $requestedMethod): void
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
            $this->gui->getObject()->getId(),
            0,
            $this->gui->getObject()->getType()
        );
        if ($requestedMethod === '') {
            $this->ilCtrl->setCmd('showSummary');
        } else {
            $this->ilCtrl->setCmd($requestedMethod);
        }
        $this->ilCtrl->forwardCommand($info);
    }
}
