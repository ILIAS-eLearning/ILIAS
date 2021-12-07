<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
        // CR: Method getCreateionForm() is deprecated
        $form = $formFactory->getCreationForm();

        if ($form->checkInput()) {
            $roomObj = $this->gui->insertObject();
            $room = ilChatroom::byObjectId($roomObj->getId());

            $connector = $this->gui->getConnector();
            // CR: Variable $response is not used
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
