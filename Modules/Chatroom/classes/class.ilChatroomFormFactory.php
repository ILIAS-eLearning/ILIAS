<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilChatroomFormFactory
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomFormFactory
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Applies given values to field in given form.
	 * @param ilPropertyFormGUI $form
	 * @param array             $values
	 */
	public static function applyValues(ilPropertyFormGUI $form, array $values)
	{
		foreach($values as $key => $value)
		{
			$field = $form->getItemByPostVar($key);
			if(!$field)
			{
				continue;
			}

			switch(strtolower(get_class($field)))
			{
				case 'ilcheckboxinputgui':
					if($value)
					{
						$field->setChecked(true);
					}
					break;

				default:
					$field->setValue($value);
			}
		}
	}

	/**
	 * Instantiates and returns ilPropertyFormGUI containing ilTextInputGUI
	 * and ilTextAreaInputGUI
	 * @deprecated replaced by default creation screens
	 * @return ilPropertyFormGUI
	 */
	public function getCreationForm()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form  = new ilPropertyFormGUI();
		$title = new ilTextInputGUI($lng->txt('title'), 'title');
		$title->setRequired(true);
		$form->addItem($title);

		$description = new ilTextAreaInputGUI($lng->txt('description'), 'desc');
		$form->addItem($description);

		return $this->addDefaultBehaviour($form);
	}

	/**
	 * Adds 'create-save' and 'cancel' button to given $form and returns it.
	 * @param ilPropertyFormGUI $form
	 * @return ilPropertyFormGUI
	 */
	private function addDefaultBehaviour(ilPropertyFormGUI $form)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form->addCommandButton('create-save', $lng->txt('create'));
		$form->addCommandButton('cancel', $lng->txt('cancel'));

		return $form;
	}

	/**
	 * @global ilLanguage $lng
	 * @return ilPropertyFormGUI
	 */
	public function getSettingsForm()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form  = new ilPropertyFormGUI();
		$title = new ilTextInputGUI($lng->txt('title'), 'title');
		$title->setRequired(true);
		$form->addItem($title);

		$description = new ilTextAreaInputGUI($lng->txt('description'), 'desc');
		$form->addItem($description);

		$cb = new ilCheckboxInputGUI($lng->txt('allow_anonymous'), 'allow_anonymous');
		$cb->setInfo($lng->txt('anonymous_hint'));

		$txt = new ilTextInputGUI($lng->txt('autogen_usernames'), 'autogen_usernames');
		$txt->setRequired(true);
		$txt->setInfo($lng->txt('autogen_usernames_info'));
		$cb->addSubItem($txt);
		$form->addItem($cb);

		$cb = new ilCheckboxInputGUI($lng->txt('allow_custom_usernames'), 'allow_custom_usernames');
		$form->addItem($cb);

		$cb_history = new ilCheckboxInputGUI($lng->txt('enable_history'), 'enable_history');
		$form->addItem($cb_history);

		$num_msg_history = new ilNumberInputGUI($lng->txt('display_past_msgs'), 'display_past_msgs');
		$num_msg_history->setInfo($lng->txt('hint_display_past_msgs'));
		$num_msg_history->setMinValue(0);
		$num_msg_history->setMaxValue(100);
		$form->addItem($num_msg_history);

		$cb = new ilCheckboxInputGUI($lng->txt('private_rooms_enabled'), 'private_rooms_enabled');
		$cb->setInfo($lng->txt('private_rooms_enabled_info'));
		$form->addItem($cb);

		return $form;
	}

	/**
	 * Prepares Fileupload form and returns it.
	 * @return ilPropertyFormGUI
	 */
	public function getFileUploadForm()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form       = new ilPropertyFormGUI();
		$file_input = new ilFileInputGUI();

		$file_input->setPostVar('file_to_upload');
		$file_input->setTitle($lng->txt('upload'));
		$form->addItem($file_input);
		$form->addCommandButton('UploadFile-uploadFile', $lng->txt('submit'));

		$form->setTarget('_blank');

		return $form;
	}

	/**
	 * Returns period form.
	 * @return ilPropertyFormGUI
	 */
	public function getPeriodForm()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form = new ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);

		require_once 'Services/Form/classes/class.ilDateDurationInputGUI.php';
		$duration = new ilDateDurationInputGUI($lng->txt('period'), 'timeperiod');

		$duration->setStartText($lng->txt('duration_from'));
		$duration->setEndText($lng->txt('duration_to'));
		$duration->setShowTime(true);
		$duration->setRequired(true);
		$form->addItem($duration);

		return $form;
	}

	/**
	 * Returns chatname selection form.
	 * @param array $name_options
	 * @return ilPropertyFormGUI
	 */
	public function getUserChatNameSelectionForm(array $name_options)
	{
		/**
		 * @var $lng    ilLanguage
		 * @var $ilUser ilObjUser
		 */
		global $lng, $ilUser;

		$form = new ilPropertyFormGUI();

		$radio = new ilRadioGroupInputGUI($lng->txt('select_custom_username'), 'custom_username_radio');

		foreach($name_options as $key => $option)
		{
			$opt = new ilRadioOption($option, $key);
			$radio->addOption($opt);
		}

		$custom_opt = new ilRadioOption($lng->txt('custom_username'), 'custom_username');
		$radio->addOption($custom_opt);

		$txt = new ilTextInputGUI($lng->txt('custom_username'), 'custom_username_text');
		$custom_opt->addSubItem($txt);
		$form->addItem($radio);

		if($ilUser->isAnonymous())
		{
			$radio->setValue('anonymousName');
		}
		else
		{
			$radio->setValue('fullname');
		}

		return $form;
	}

	/**
	 * Returns session form with period set by given $sessions.
	 * @param array $sessions
	 * @return ilPropertyFormGUI
	 */
	public function getSessionForm(array $sessions)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form = new ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);
		$list = new ilSelectInputGUI($lng->txt('session'), 'session');

		$options = array();

		foreach($sessions as $session)
		{
			$start = new ilDateTime($session['connected'], IL_CAL_UNIX);
			$end   = new ilDateTime($session['disconnected'], IL_CAL_UNIX);

			$options[$session['connected'] . ',' .
			$session['disconnected']] = ilDatePresentation::formatPeriod($start, $end);
		}

		$list->setOptions($options);
		$list->setRequired(true);

		$form->addItem($list);

		return $form;
	}

	/**
	 * Returns general settings form.
	 * @return ilPropertyFormGUI
	 */
	public function getGeneralSettingsForm()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form = new ilPropertyFormGUI();

		$address = new ilTextInputGUI($lng->txt('chatserver_address'), 'address');
		$address->setRequired(true);
		$form->addItem($address);

		$port = new ilNumberInputGUI($lng->txt('chatserver_port'), 'port');
		$port->setMinValue(1);
		$port->setMaxValue(65535);
		$port->setRequired(true);
		$port->setInfo($lng->txt('port_info'));
		$port->setSize(6);
		$form->addItem($port);

		$protocol = new ilRadioGroupInputGUI($lng->txt('protocol'), 'protocol');
		$form->addItem($protocol);

		$http = new ilRadioOption($lng->txt('http'), 'http');
		$protocol->addOption($http);

		$https = new ilRadioOption($lng->txt('https'), 'https');
		$protocol->addOption($https);

		$certificate = new ilTextInputGUI($lng->txt('certificate'), 'cert');
		$certificate->setRequired(true);
		$https->addSubItem($certificate);

		$key = new ilTextInputGUI($lng->txt('key'), 'key');
		$key->setRequired(true);
		$https->addSubItem($key);

		$dhparam = new ilTextInputGUI($lng->txt('dhparam'), 'dhparam');
		$dhparam->setRequired(true);
		$https->addSubItem($dhparam);

		$chatLog = new ilTextInputGUI($lng->txt('log'), 'log');
		$chatLog->setRequired(false);
		$form->addItem($chatLog);

		$chatErrorLog = new ilTextInputGUI($lng->txt('error_log'), 'error_log');
		$chatErrorLog->setRequired(false);
		$form->addItem($chatErrorLog);

		$iliasSection = new ilFormSectionHeaderGUI();
		$iliasSection->setTitle($lng->txt('ilias_chatserver_connection'));
		$form->addItem($iliasSection);

		$iliasProxy = new ilCheckboxInputGUI($lng->txt('proxy'), 'ilias_proxy');
		$iliasProxy->setRequired(false);
		$iliasProxy->setInfo($lng->txt('ilias_proxy_info'));
		$form->addItem($iliasProxy);

		$chatServerILIASUrl = new ilTextInputGUI($lng->txt('url'), 'ilias_url');
		$chatServerILIASUrl->setRequired(true);
		$chatServerILIASUrl->setInfo($lng->txt('connection_url_info'));
		$iliasProxy->addSubItem($chatServerILIASUrl);

		$clientSection = new ilFormSectionHeaderGUI();
		$clientSection->setTitle($lng->txt('client_chatserver_connection'));
		$form->addItem($clientSection);

		$clientProxy = new ilCheckboxInputGUI($lng->txt('proxy'), 'client_proxy');
		$clientProxy->setRequired(false);
		$clientProxy->setInfo($lng->txt('client_proxy_info'));
		$form->addItem($clientProxy);

		$chatServerClientUrl = new ilTextInputGUI($lng->txt('url'), 'client_url');
		$chatServerClientUrl->setRequired(true);
		$chatServerClientUrl->setInfo($lng->txt('connection_url_info'));
		$clientProxy->addSubItem($chatServerClientUrl);

		return $form;
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	public function getClientSettingsForm()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$form = new ilPropertyFormGUI();

		$cb = new ilCheckboxInputGUI($lng->txt('chat_enabled'), 'chat_enabled');
		$form->addItem($cb);

		$cb = new ilCheckboxInputGUI($lng->txt('enable_osd'), 'enable_osd');
		$cb->setInfo($lng->txt('hint_osd'));
		$form->addItem($cb);

		$txt = new ilNumberInputGUI($lng->txt('osd_intervall'), 'osd_intervall');
		$txt->setMinValue(1);
		$txt->setRequired(true);
		$txt->setInfo($lng->txt('hint_osd_interval'));
		$cb->addSubItem($txt);

		$cb1 = new ilCheckboxInputGUI($lng->txt('play_invitation_sound'), 'play_invitation_sound');
		$cb1->setInfo($lng->txt('play_invitation_sound'));
		$cb->addSubItem($cb1);

		$cb = new ilCheckboxInputGUI($lng->txt('enable_smilies'), 'enable_smilies');
		$cb->setInfo($lng->txt('hint_enable_smilies'));
		$form->addItem($cb);

		require_once 'Modules/Chatroom/classes/class.ilChatroomAuthInputGUI.php';
		$auth = new ilChatroomAuthInputGUI($lng->txt('chatroom_auth'), 'auth');
		$auth->setCtrlPath(array('iladministrationgui', 'ilobjchatroomgui', 'ilpropertyformgui', 'ilformpropertydispatchgui', 'ilchatroomauthinputgui'));
		$auth->setRequired(true);
		$form->addItem($auth);

		return $form;
	}
}