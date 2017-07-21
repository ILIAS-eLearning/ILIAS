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
	 * Constructor
	 * Requires ilInfoScreenGUI and sets $this->gui using given $gui.
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		parent::__construct($gui);
		require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
	}

	/**
	 * Prepares and displays the info screen.
	 * @global ilCtrl2    $ilCtrl
	 * @global ilLanguage $lng
	 * @param string      $method
	 */
	public function executeDefault($method)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 * @var $ilCtrl     ilCtrl
		 * @var $lng        ilLanguage
		 */
		global $rbacsystem, $ilCtrl, $lng;

		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		$this->redirectIfNoPermission('read');

		$this->gui->switchToVisibleMode();

		if(!ilChatroom::checkUserPermissions("visible", $this->gui->ref_id, false))
		{
			$this->gui->ilias->raiseError(
				$lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE
			);
		}

		$info = $this->createInfoScreenGUI($this->gui);

		$info->enablePrivateNotes();

		if(ilChatroom::checkUserPermissions("read", (int)$_GET["ref_id"], false))
		{
			$info->enableNews();
		}

		$info->addMetaDataSections(
			$this->gui->object->getId(), 0, $this->gui->object->getType()
		);
		if(!$method)
		{
			$ilCtrl->setCmd('showSummary');
		}
		else
		{
			$ilCtrl->setCmd($method);
		}
		$ilCtrl->forwardCommand($info);
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