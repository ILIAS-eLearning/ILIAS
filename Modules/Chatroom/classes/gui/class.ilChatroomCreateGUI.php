<?php
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
    /**
     * Inserts new object into gui.
     */
    public function save()
    {
        require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
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

    /**
     * {@inheritdoc}
     */
    public function executeDefault($method)
    {
        $this->gui->switchToVisibleMode();
        $this->gui->createObject();
        return;
    }
}
