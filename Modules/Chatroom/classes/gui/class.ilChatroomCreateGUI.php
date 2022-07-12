<?php declare(strict_types=1);

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
 * Class ilChatroomCreateGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 * @TODO    IN USE?
 */
class ilChatroomCreateGUI extends ilChatroomGUIHandler
{
    public function save() : void
    {
        $formFactory = new ilChatroomFormFactory();
        $form = $formFactory->getCreationForm();

        if ($form->checkInput()) {
            $roomObj = $this->gui->insertObject();
            $room = ilChatroom::byObjectId($roomObj->getId());

            $connector = $this->gui->getConnector();
            $response = $connector->sendCreatePrivateRoom($room->getRoomId(), 0, $roomObj->getOwner(), $roomObj->getTitle());

            $this->ilCtrl->setParameter($this->gui, 'ref_id', $this->gui->getRefId());
            $this->ilCtrl->redirect($this->gui, 'settings-general');
        } else {
            $this->executeDefault('create');
        }
    }

    public function executeDefault(string $requestedMethod) : void
    {
        $this->gui->switchToVisibleMode();
        $this->gui->createObject();
    }
}
