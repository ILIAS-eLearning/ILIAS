<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSettingsGUI
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSettingsGUI extends ilChatroomGUIHandler
{
	/**
	 * Constructor
	 * Requires ilChatroomFormFactory, ilChatroom and ilChatroomInstaller,
	 * sets $this->gui using given $gui and calls ilChatroomInstaller::install()
	 * method.
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		parent::__construct($gui);

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
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
			$settingsForm->setValuesByPost();
			$this->general($settingsForm);
		}
		else
		{
			$this->gui->object->setTitle($settingsForm->getInput('title'));
			$this->gui->object->setDescription($settingsForm->getInput('desc'));

			require_once 'Services/Object/classes/class.ilObjectActivation.php';

			$period = $settingsForm->getItemByPostVar('access_period');
			if($period->getStart() && $period->getEnd())
			{
				$this->gui->object->setAccessType(ilObjectActivation::TIMINGS_ACTIVATION);
				$this->gui->object->setAccessBegin($period->getStart()->get(IL_CAL_UNIX));
				$this->gui->object->setAccessEnd($period->getEnd()->get(IL_CAL_UNIX));
				$this->gui->object->setAccessVisibility((int)$settingsForm->getInput('access_visibility'));
			}
			else
			{
				$this->gui->object->setAccessType(ilObjectActivation::TIMINGS_DEACTIVATED);
			}

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

		$room = ilChatRoom::byObjectId($this->gui->object->getId());

		if(!$settingsForm)
		{
			$settingsForm = $formFactory->getSettingsForm();

			require_once 'Services/Object/classes/class.ilObjectActivation.php';
			$settings = array(
				'title'                 => $this->gui->object->getTitle(),
				'desc'                  => $this->gui->object->getDescription(),
				'access_period'         => array(
					'start' => $this->gui->object->getAccessBegin() ? new ilDateTime($this->gui->object->getAccessBegin(), IL_CAL_UNIX) : null,
					'end'   => $this->gui->object->getAccessEnd()   ? new ilDateTime($this->gui->object->getAccessEnd(), IL_CAL_UNIX) : null
				),
				'access_visibility' => $this->gui->object->getAccessVisibility()
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
		}

		$settingsForm->setTitle($lng->txt('settings_title'));
		$settingsForm->addCommandButton('settings-saveGeneral', $lng->txt('save'));
		$settingsForm->setFormAction($ilCtrl->getFormAction($this->gui, 'settings-saveGeneral'));

		$tpl->setVariable('ADM_CONTENT', $settingsForm->getHtml());
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
