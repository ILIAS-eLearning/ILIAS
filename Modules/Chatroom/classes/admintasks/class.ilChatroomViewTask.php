<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

/**
 * Class ilChatroomViewTask
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomViewTask extends ilChatroomTaskHandler
{
	/**
	 * @var ilChatroomObjectGUI
	 */
	private $gui;

	/**
	 * @var ilSetting
	 */
	private $commonSettings;

	/**
	 * Constructor
	 * Sets $this->gui using given $gui
	 * @param ilChatroomObjectGUI $gui
	 */
	public function __construct(ilChatroomObjectGUI $gui)
	{
		$this->gui            = $gui;
		$this->commonSettings = new ilSetting('common');
	}

	/**
	 *
	 */
	private function showSoapWarningIfNeeded()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		if(!$this->commonSettings->get('soap_user_administration'))
		{
			ilUtil::sendInfo($lng->txt('soap_must_be_enabled'));
		}
	}

	/**
	 * Saves settings fetched from $_POST
	 */
	public function saveSettings()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $ilCtrl, $lng;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		$factory = new ilChatroomFormFactory();
		$form    = $factory->getGeneralSettingsForm();

		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->serversettings($form);
			return;
		}

		if(!$this->checkPrivHosts($form->getInput('priv_hosts')))
		{
			$form->setValuesByPost();
			ilUtil::sendFailure($lng->txt('invalid_priv_hosts'));
			$this->serversettings($form);
			return;
		}

		$settings = array(
			'protocol'   => $form->getInput('protocol'),
			'instance'   => $form->getInput('instance'),
			'port'       => $form->getInput('port'),
			'address'    => $form->getInput('address'),
			'priv_hosts' => $form->getInput('priv_hosts'),
			'keystore'   => $form->getInput('keystore'),
			'keypass'    => $form->getInput('keypass'),
			'storepass'  => $form->getInput('storepass')
		);

		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
		$adminSettings = new ilChatroomAdmin($this->gui->object->getId());
		$adminSettings->saveGeneralSettings((object)$settings);

		$this->writeDataToFile($settings);

		ilUtil::sendSuccess($lng->txt('settings_has_been_saved'), true);
		$ilCtrl->redirect($this->gui, 'view-serversettings');
	}

	/**
	 * Saves client settings fetched from $_POST
	 */
	public function saveClientSettings()
	{
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $ilCtrl, $lng;

		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		$factory = new ilChatroomFormFactory();
		$form    = $factory->getClientSettingsForm();

		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->clientsettings($form);
			return;
		}

		$settings = array(
			'hash'                  => $form->getInput('name'),
			'name'                  => $form->getInput('name'),
			'url'                   => $form->getInput('url'),
			'user'                  => $form->getInput('user'),
			'password'              => $form->getInput('password'),
			'client'                => CLIENT_ID,
			'enable_osd'            => (boolean)$form->getInput('enable_osd'),
			'osd_intervall'         => (int)$form->getInput('osd_intervall'),
			'chat_enabled'          => ((boolean)$form->getInput('chat_enabled')) && ((boolean)$this->commonSettings->get('soap_user_administration')),
			'enable_smilies'        => (boolean)$form->getInput('enable_smilies'),
			'play_invitation_sound' => (boolean)$form->getInput('play_invitation_sound')
		);

		$notificationSettings = new ilSetting('notifications');
		$notificationSettings->set('osd_polling_intervall', (int)$form->getInput('osd_intervall'));
		$notificationSettings->set('enable_osd', (boolean)$form->getInput('enable_osd'));

		$chatSettings = new ilSetting('chatroom');
		$chatSettings->set('chat_enabled', $settings['chat_enabled']);
		$chatSettings->set('play_invitation_sound', (boolean)$form->getInput('play_invitation_sound'));

		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
		$adminSettings = new ilChatroomAdmin($this->gui->object->getId());
		$adminSettings->saveClientSettings((object)$settings);

		$this->writeClientSettingsToFile($settings);

		ilUtil::sendSuccess($lng->txt('settings_has_been_saved'), true);

		$ilCtrl->redirect($this->gui, 'view-clientsettings');
	}

	/**
	 * Writes client settings to client.properties file
	 * @param array $settings
	 * @throws Exception
	 */
	protected function writeClientSettingsToFile(array $settings)
	{
		if($srv_prp_path = $this->checkDirectory())
		{
			$handle = fopen($srv_prp_path . 'client.properties', 'w');

			if(!fwrite($handle, $this->getClientFileContent($settings)))
			{
				throw new Exception('Cannot write to file');
			}

			fclose($handle);
		}
	}

	/**
	 * Formats content for client settings file
	 * @param array $settings
	 * @return string
	 */
	protected function getClientFileContent(array $settings)
	{
		$linebreak = "\n";

		$content = 'hash = ' . $settings['hash'] . $linebreak;
		$content .= 'name = ' . $settings['name'] . $linebreak;
		$content .= 'url = ' . $settings['url'] . $linebreak;
		$content .= 'user = ' . $settings['user'] . $linebreak;
		$content .= 'password = ' . $settings['password'] . $linebreak;
		$content .= 'client = ' . $settings['client'];

		return $content;
	}


	/**
	 * Writes server settings to server.properties file
	 * @param array $settings
	 */
	protected function writeDataToFile(array $settings)
	{
		if($srv_prp_path = $this->checkDirectory())
		{
			$handle = fopen($srv_prp_path . 'server.properties', 'w');

			if(!fwrite($handle, $this->getFileContent($settings)))
			{
				throw new Exception('Cannot write to file');
			}

			fclose($handle);
		}
	}

	/**
	 * Builds and formats content tot write in server.properties file.
	 * @param array $settings
	 * @return string
	 */
	protected function getFileContent(array $settings)
	{
		$linebreak = "\n";

		$content = 'host = ' . $settings['address'] . $linebreak;
		$content .= 'port = ' . $settings['port'] . $linebreak;
		$content .= 'privileged_hosts = ' . $settings['priv_hosts'] . $linebreak;

		$settings['protocol'] == 'https' ? $https = 1 : $https = 0;

		$content .= 'https = ' . $https . $linebreak;
		$content .= 'keystore = ' . $settings['keystore'] . $linebreak;
		$content .= 'keypass = ' . $settings['keypass'] . $linebreak;
		$content .= 'storepass = ' . $settings['storepass'];

		return $content;
	}

	/**
	 * Checks if external chatroom directory exists or can be created.
	 * @return string
	 * @throws Exception
	 */
	protected function checkDirectory()
	{
		$srv_prp_path = ilUtil::getDataDir() . '/chatroom/';

		if(!file_exists($srv_prp_path))
		{
			if(!ilUtil::makeDir($srv_prp_path))
			{
				throw new Exception('Directory cannot be created');
			}
		}

		return $srv_prp_path;
	}


	/**
	 * Checks if a valid IP number or a comma-separated string of valid
	 * IP numbers is given.
	 * @param string $ipnumbers
	 * @return bool
	 */
	protected function checkPrivHosts($ipnumbers)
	{
		$ipnumbers = preg_replace("/[^0-9.,]+/", "", $ipnumbers);
		$ips       = explode(',', $ipnumbers);

		foreach($ips as $ip)
		{
			$ip_parts = explode('.', $ip);

			if(!($ip_parts[0] <= 255 && $ip_parts[1] <= 255 && $ip_parts[2] <= 255 && $ip_parts[3] <= 255 &&
				preg_match("!^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$!", $ip))
			)
			{
				return false;
			}
		}

		return true;
	}


	/**
	 * Prepares view form and displays it.
	 * @param ilPropertyFormGUI $form
	 */
	public function serversettings(ilPropertyFormGUI $form = null)
	{
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $tpl, $ilCtrl, $lng;

		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		ilChatroom::checkUserPermissions('read', $this->gui->ref_id);

		$this->defaultActions();
		$this->gui->switchToVisibleMode();
		$this->showSoapWarningIfNeeded();

		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
		$adminSettings  = new ilChatroomAdmin($this->gui->object->getId());
		$serverSettings = (array)$adminSettings->loadGeneralSettings();
		if($form === null)
		{
			require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
			$factory = new ilChatroomFormFactory();
			$form    = $factory->getGeneralSettingsForm();

			if(!$serverSettings['protocol'])
			{
				$serverSettings['protocol'] = 'http';
			}

			$form->setValuesByArray($serverSettings);
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';

		if($serverSettings['port'] && $serverSettings['address'] && !(boolean)@ilChatroomServerConnector::checkServerConnection())
		{
			ilUtil::sendInfo($lng->txt('chat_cannot_connect_to_server'));
		}

		$form->setTitle($lng->txt('chatserver_settings_title'));
		$form->addCommandButton('view-saveSettings', $lng->txt('save'));
		$form->setFormAction($ilCtrl->getFormAction($this->gui, 'view-saveSettings'));
		$serverTpl = new ilTemplate('tpl.chatroom_serversettings.html', true, true, 'Modules/Chatroom');

		$serverTpl->setVariable('VAL_SERVERSETTINGS_FORM', $form->getHTML());
		$serverTpl->setVariable('LBL_SERVERSETTINGS_FURTHER_INFORMATION', sprintf($lng->txt('server_further_information'), ilUtil::_getHttpPath() . '/Modules/Chatroom/server/README.txt'));

		$tpl->setVariable('ADM_CONTENT', $serverTpl->get());
	}

	/**
	 * Calls this->view() method
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		/**
		 * @var $ilCtrl ilCtrl
		 */
		global $ilCtrl;

		$ilCtrl->redirect($this->gui, 'view-clientsettings');
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function clientsettings(ilPropertyFormGUI $form = null)
	{
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 * @var $lng    ilLanguage
		 */
		global $tpl, $ilCtrl, $lng;

		ilChatroom::checkUserPermissions('read', $this->gui->ref_id);

		$this->defaultActions();

		$this->gui->switchToVisibleMode();

		$this->showSoapWarningIfNeeded();

		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
		$adminSettings = new ilChatroomAdmin($this->gui->object->getId());

		if($form === null)
		{
			require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
			$factory = new ilChatroomFormFactory();
			$form    = $factory->getClientSettingsForm();

			if(!$this->commonSettings->get('soap_user_administration'))
			{
				$form->getItemByPostVar('chat_enabled')->setDisabled(!(boolean)$this->commonSettings->get('soap_user_administration'));
				$form->getItemByPostVar('chat_enabled')->setChecked(0);
			}

			$data = (array)$adminSettings->loadClientSettings();

			if(!$data['osd_intervall'])
			{
				$data['osd_intervall'] = 60;
			}

			if(!$data)
			{
				$data = array();
			}

			if(!$data['url'])
			{
				$data['url'] = ilUtil::_getHttpPath();
			}

			if(!$data['client'])
			{
				$data['client'] = CLIENT_ID;
			}

			$data['password_retype'] = $data['password'];
			$form->setValuesByArray($data);
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';

		$serverSettings = (array)$adminSettings->loadGeneralSettings();
		if($serverSettings['port'] && $serverSettings['address'] && !(boolean)@ilChatroomServerConnector::checkServerConnection())
		{
			ilUtil::sendInfo($lng->txt('chat_cannot_connect_to_server'));
		}

		$form->setTitle($lng->txt('general_settings_title'));
		$form->addCommandButton('view-saveClientSettings', $lng->txt('save'));
		$form->setFormAction($ilCtrl->getFormAction($this->gui, 'view-saveClientSettings'));

		$settingsTpl = new ilTemplate('tpl.chatroom_serversettings.html', true, true, 'Modules/Chatroom');

		$settingsTpl->setVariable('VAL_SERVERSETTINGS_FORM', $form->getHTML());
		$settingsTpl->setVariable('LBL_SERVERSETTINGS_FURTHER_INFORMATION', sprintf($lng->txt('server_further_information'), ilUtil::_getHttpPath() . '/Modules/Chatroom/server/README.txt'));

		$tpl->setVariable('ADM_CONTENT', $settingsTpl->get());
	}

	/**
	 *
	 */
	private function defaultActions()
	{
		$chatSettings = new ilSetting('chatroom');
		if($chatSettings->get('chat_enabled', false))
		{
			$this->forcePublicRoom();
		}
	}

	/**
	 *
	 */
	public function forcePublicRoom()
	{
		$ref_id = ilObjChatroom::_getPublicRefId();
		if(!$ref_id)
		{
			$this->createPublicRoom();
			return;
		}

		$instance = ilObjectFactory::getInstanceByRefId($ref_id, false);
		if(!$instance)
		{
			$this->createPublicRoom();
			return;
		}

		$obj_id = ilObject::_lookupObjId($ref_id);
		if(!$obj_id)
		{
			$this->createPublicRoom();
			return;
		}

		if(!ilObject::_hasUntrashedReference($obj_id))
		{
			$this->createPublicRoom();
			return;
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
		ilChatroomInstaller::ensureCorrectPublicChatroomTreeLocation($ref_id);
	}

	/**
	 *
	 */
	public function createPublicRoom()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		require_once 'Modules/Chatroom/classes/class.ilChatroomInstaller.php';
		ilChatroomInstaller::createDefaultPublicRoom(true);
		ilUtil::sendSuccess($lng->txt('public_chat_created'), true);
	}
}