<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomCreateTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 *
 * @TODO IN USE?
 */
class ilChatroomCreateTask extends ilChatroomTaskHandler
{

	/**
	 * Switches gui to visible mode. Instantiates and prepares form.
	 *
	 * @global ilTemplate $tpl
	 * @global ilObjUser $ilUser
	 * @global ilCtrl2 $ilCtrl
	 * @param string $method
	 */
	public function executeDefault($method)
	{
	    $this->gui->switchToVisibleMode();
	    return $this->gui->createObject();
 	}
 
	/**
	 * Inserts new object into gui.
	 *
	 * @global ilCtrl2 $ilCtrl
	 */
	public function save()
	{
		global $ilCtrl;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		$formFactory = new ilChatroomFormFactory();
		$form = $formFactory->getCreationForm();

		if( $form->checkInput() )
		{
			$roomObj = $this->gui->insertObject();
			$room = ilChatroom::byObjectId($roomObj->getId());

			$connector = $this->gui->getConnector();
			$response  = $connector->sendCreatePrivateRoom($room->getRoomId(), 0, $roomObj->getOwner(), $roomObj->getTitle());


			$ilCtrl->setParameter( $this->gui, 'ref_id', $this->gui->getRefId() );
			$ilCtrl->redirect( $this->gui, 'settings-general' );
		}
		else
		{
			$this->executeDefault( 'create' );
		}
	}

}

?>