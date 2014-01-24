<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomInfoTask
 *
 * Provides methods to prepare and display the info task.
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomInfoTask extends ilChatroomTaskHandler
{
	private $gui;

	/**
	 * Constructor
	 *
	 * Requires ilInfoScreenGUI and sets $this->gui using given $gui.
	 *
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;
		require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
	}

	/**
	 * Prepares and displays the info screen.
	 *
	 * @global ilCtrl2 $ilCtrl
	 * @global ilLanguage $lng
	 * @param string $method
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

		if(!ilChatroom::checkUserPermissions('read', $this->gui->ref_id))
		{
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
			$ilCtrl->redirectByClass("ilrepositorygui", "");
		}

		$this->gui->switchToVisibleMode();

		if(!$rbacsystem->checkAccess("visible", $this->gui->ref_id))
		{
			$this->gui->ilias->raiseError(
				$lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE
			);
		}

		$info = new ilInfoScreenGUI($this->gui);

		$info->enablePrivateNotes();

		if($rbacsystem->checkAccess("read", (int)$_GET["ref_id"]))
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
}