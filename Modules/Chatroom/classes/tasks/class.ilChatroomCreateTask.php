<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomCreateTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomCreateTask extends ilDBayTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui
	 *
	 * @param ilDBayObjectGUI $gui
	 */
	public function __construct(ilDBayObjectGUI $gui)
	{
		$this->gui = $gui;
	}

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
			$this->gui->insertObject();
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