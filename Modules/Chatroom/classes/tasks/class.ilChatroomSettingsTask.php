<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSettingsTask
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSettingsTask extends ilChatroomTaskHandler
{
	/**
	 * @var ilChatroomObjectGUI
	 */
	private $gui;

	/**
	 * Constructor
	 * Requires ilChatroomFormFactory, ilChatroom and ilChatroomInstaller,
	 * sets $this->gui using given $gui and calls ilChatroomInstaller::install()
	 * method.
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui = $gui;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
	}

	/**
	 * Prepares and displays settings form.
	 * @param ilPropertyFormGUI $settingsForm
	 */
	public function general(ilPropertyFormGUI $settingsForm = null)
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $tpl, $ilCtrl;

		if(!ilChatroom::checkUserPermissions(array(
			'read',
			'write'
		), $this->gui->ref_id)
		)
		{
			$ilCtrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
			$ilCtrl->redirectByClass('ilrepositorygui', '');
		}

		$chatSettings = new ilSetting('chatroom');
		if(!$chatSettings->get('chat_enabled'))
		{
			ilUtil::sendInfo($lng->txt('server_disabled'), true);
		}

		$this->gui->switchToVisibleMode();

		$formFactory = new ilChatroomFormFactory();

		if(!$settingsForm)
		{
			$settingsForm = $formFactory->getSettingsForm();
		}

		$room = ilChatRoom::byObjectId($this->gui->object->getId());

		$settings = array(
			'title' => $this->gui->object->getTitle(),
			'desc'  => $this->gui->object->getDescription(),
		);

		if($room)
		{
			ilChatroomFormFactory::applyValues(
				$settingsForm, array_merge($settings, $room->getSettings())
			);
		}
		else
		{
			ilChatroomFormFactory::applyValues($settingsForm, $settings);
		}

		$settingsForm->setTitle($lng->txt('settings_title'));
		$settingsForm->addCommandButton('settings-saveGeneral', $lng->txt('save'));
		$settingsForm->setFormAction($ilCtrl->getFormAction($this->gui, 'settings-saveGeneral'));

		$tpl->setVariable('ADM_CONTENT', $settingsForm->getHtml());
	}

	/**
	 * Saves settings fetched from $_POST.
	 */
	public function saveGeneral()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $ilCtrl, $lng;

		$formFactory  = new ilChatroomFormFactory();
		$settingsForm = $formFactory->getSettingsForm();

		if(!$settingsForm->checkInput())
		{
			$this->general($settingsForm);
		}
		else
		{
			$this->gui->object->setTitle($settingsForm->getInput('title'));
			$this->gui->object->setDescription($settingsForm->getInput('desc'));
			$this->gui->object->update();
			// @todo: Do not rely on raw post data
			$settings = $_POST;
			$room     = ilChatRoom::byObjectId($this->gui->object->getId());

			if(!$room)
			{
				$room                  = new ilChatRoom();
				$settings['object_id'] = $this->gui->object->getId();
			}
			$room->saveSettings($settings);

			ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
			$ilCtrl->redirect($this->gui, 'settings-general');
		}
	}

	/**
	 * @param string $requestedMethod
	 * @return void
	 */
	public function executeDefault($requestedMethod)
	{
		$this->general();
	}
}
