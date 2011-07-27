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
class ilChatroomInfoTask extends ilDBayTaskHandler
{
	private $gui;

	/**
	 * Constructor
	 *
	 * Requires ilInfoScreenGUI and sets $this->gui using given $gui.
	 *
	 * @param ilDBayObjectGUI $gui
	 */
	public function __construct(ilDBayObjectGUI $gui)
	{
		$this->gui = $gui;
		require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
	}

	/**
	 * Prepares and displays the info screen.
	 *
	 * @global ilAccessHandler $ilAccess
	 * @global ilCtrl2 $ilCtrl
	 * @global ilLanguage $lng
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		global $ilAccess, $ilCtrl, $lng;

		$this->gui->switchToVisibleMode();

		if( !$ilAccess->checkAccess( "visible", "", $this->gui->ref_id ) )
		{
			$this->gui->ilias->raiseError(
			$lng->txt( "msg_no_perm_read" ), $this->ilias->error_obj->MESSAGE
			);
		}

		$info = new ilInfoScreenGUI( $this->gui );

		$info->enablePrivateNotes();

		if( $ilAccess->checkAccess( "read", "", $_GET["ref_id"] ) )
		{
			$info->enableNews();
		}

		$info->addMetaDataSections(
		$this->gui->object->getId(), 0, $this->gui->object->getType()
		);
		$ilCtrl->setCmd( 'showSummary' );
		$ilCtrl->forwardCommand( $info );
	}

}

?>