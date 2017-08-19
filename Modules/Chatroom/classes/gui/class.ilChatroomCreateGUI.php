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
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		$formFactory = new ilChatroomFormFactory();
		$form        = $formFactory->getCreationForm();

		if($form->checkInput())
		{
			$roomObj = $this->gui->insertObject();
			$room    = ilChatroom::byObjectId($roomObj->getId());

			$connector = $this->gui->getConnector();
			$response  = $connector->sendCreatePrivateRoom($room->getRoomId(), 0, $roomObj->getOwner(), $roomObj->getTitle());

			$ilCtrl->setParameter($this->gui, 'ref_id', $this->gui->getRefId());
			$ilCtrl->redirect($this->gui, 'settings-general');
		}
		else
		{
			$this->executeDefault('create');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function executeDefault($method)
	{
		$this->gui->switchToVisibleMode();
		return $this->gui->createObject();
	}
}