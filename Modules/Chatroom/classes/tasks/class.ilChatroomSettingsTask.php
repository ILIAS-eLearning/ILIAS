<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomSettingsTask
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomSettingsTask extends ilDBayTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Requires ilChatroomFormFactory, ilChatroom and ilChatroomInstaller,
	 * sets $this->gui using given $gui and calls ilChatroomInstaller::install()
	 * method.
	 *
	 * @param ilDBayObjectGUI $gui
	 */
	public function __construct(ilDBayObjectGUI $gui)
    {
	    $this->gui = $gui;

	    require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
	    require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
	    //ilChatroomInstaller::install();
	}

	/**
	 * Prepares and displays settings form.
	 *
	 * @global ilLanguage $lng
	 * @global ilTemplate $tpl
	 * @global ilCtrl2 $ilCtrl
	 * @param ilPropertyFormGUI $settingsForm
	 */
	public function general(ilPropertyFormGUI $settingsForm = null)
	{
	    global $lng, $tpl, $ilCtrl;

	    if ( !ilChatroom::checkUserPermissions( array('read', 'write') , $this->gui->ref_id ) )
	    {
	    	$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
	    	$ilCtrl->redirectByClass("ilrepositorygui", "");
	    }

	    $chatSettings = new ilSetting('chatroom');
	    if( !$chatSettings->get('chat_enabled') )
	    {
		ilUtil::sendInfo($lng->txt('server_disabled'), true);
	    }

	    $this->gui->switchToVisibleMode();

	    $formFactory = new ilChatroomFormFactory();

	    if( !$settingsForm )
	    {
		$settingsForm = $formFactory->getSettingsForm();
	    }

	    $room = ilChatRoom::byObjectId( $this->gui->object->getId() );

	    $settings = array(
		'title' => $this->gui->object->getTitle(),
		'desc'  => $this->gui->object->getDescription(),
	    );

	    if( $room )
	    {
		//$settingsForm->setValuesByArray(array_merge($settings, $room->getSettings()));
		ilChatroomFormFactory::applyValues(
		    $settingsForm, array_merge( $settings, $room->getSettings() )
		);
	    }
	    else
	    {
		//$settingsForm->setValuesByArray($settings);
		ilChatroomFormFactory::applyValues( $settingsForm, $settings );
	    }

	    $settingsForm->setTitle( $lng->txt('settings_title') );
	    $settingsForm->addCommandButton( 'settings-saveGeneral', $lng->txt( 'save' ) );
	    $settingsForm->setFormAction(
		$ilCtrl->getFormAction( $this->gui, 'settings-saveGeneral' )
	    );

	    $tpl->setVariable( 'ADM_CONTENT', $settingsForm->getHtml() );
	}

	/**
	 * Saves settings fetched from $_POST.
	 *
	 * @global ilCtrl2 $ilCtrl
	 */
	public function saveGeneral()
	{
	    global $ilCtrl, $lng;

	    $formFactory	= new ilChatroomFormFactory();
	    $settingsForm	= $formFactory->getSettingsForm();

	    if( !$settingsForm->checkInput() )
	    {
		$this->general( $settingsForm );
	    }
	    else
	    {
		$this->gui->object->setTitle( $_POST['title'] );
		$this->gui->object->setDescription( $_POST['desc'] );
		$this->gui->object->update();

		$settings   = $_POST;
		$room	    = ilChatRoom::byObjectId( $this->gui->object->getId() );

		if( !$room )
		{
		    $room = new ilChatRoom();
		    $settings['object_id'] = $this->gui->object->getId();
		}
//var_dump($settings);exit;
		$room->saveSettings( $settings );
		
		ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
		
		$ilCtrl->redirect( $this->gui, 'settings-general' );
	    }
	}

	/**
	 * executeDefault
	 *
	 * @param string $requestedMethod
	 */
	public function executeDefault($requestedMethod)
	{
	    $this->general();
	}

}

?>
