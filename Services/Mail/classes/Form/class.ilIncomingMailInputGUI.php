<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Mail/classes/class.ilMailOptions.php';
include_once 'Services/Form/classes/class.ilRadioGroupInputGUI.php';
include_once 'Services/Form/classes/class.ilRadioOption.php';
/**
 * Class ilIncomingMailInputGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilIncomingMailInputGUI extends ilRadioGroupInputGUI
{
	/**
	 * ilIncomingMailInputGUI constructor.
	 * @param string $title
	 * @param string $post_var
	 */
	public function __construct($title = '', $post_var = '')	
	{
		parent::__construct($title, $post_var);
		$this->addSubOptions();
	}
	
	private function addSubOptions()
	{
		global $ilUser, $ilSetting, $lng;
		
		$r_opt1 = new ilRadioOption($lng->txt('mail_incoming_local'), IL_MAIL_LOCAL);
		$this->addOption($r_opt1);
		
		$r_opt2          = new ilRadioOption($lng->txt('mail_incoming_smtp'), IL_MAIL_EMAIL);
		$sub_radio_group = new ilRadioGroupInputGUI('', 'mail_address_option');
		$sub_mail_opt1   = new ilRadioOption($lng->txt('mail_first_email'), IL_MAIL_FIRST_EMAIL);
		$sub_radio_group->addOption($sub_mail_opt1);
		
		$sub_mail_opt2 = new ilRadioOption($lng->txt('mail_second_email'), IL_MAIL_SECOND_EMAIL);
		$sub_radio_group->addOption($sub_mail_opt2);
		
		$sub_mail_opt3 = new ilRadioOption($lng->txt('mail_both_email'), IL_MAIL_BOTH_EMAIL);
		$sub_radio_group->addOption($sub_mail_opt3);
		$r_opt2->addSubItem($sub_radio_group);
		$this->addOption($r_opt2);
		
		$r_opt3          = new ilRadioOption($lng->txt('mail_incoming_both'), IL_MAIL_BOTH);
		$sub_radio_group = new ilRadioGroupInputGUI('', 'mail_address_option_both');
		$sub_both_opt1   = new ilRadioOption($lng->txt('mail_first_email'), IL_MAIL_FIRST_EMAIL);
		$sub_radio_group->addOption($sub_both_opt1);
		
		$sub_both_opt2 = new ilRadioOption($lng->txt('mail_second_email'), IL_MAIL_SECOND_EMAIL);
		$sub_radio_group->addOption($sub_both_opt2);
		
		$sub_both_opt3 = new ilRadioOption($lng->txt('mail_both_email'), IL_MAIL_BOTH_EMAIL);
		$sub_radio_group->addOption($sub_both_opt3);
		
		if($this->getContext() == 'ilmailoptionsgui')
		{
			if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())) ||
				$ilSetting->get('usr_settings_disable_mail_incoming_mail') == '1'
			)
			{
				$this->setDisabled(true);
			}
			
			if(!strlen($ilUser->getEmail()))
			{
				$sub_mail_opt1->setDisabled(true);
				$sub_mail_opt3->setDisabled(true);
				$sub_both_opt1->setDisabled(true);
				$sub_both_opt3->setDisabled(true);
			}
			if(!strlen($ilUser->getSecondEmail()))
			{
				$sub_mail_opt2->setDisabled(true);
				$sub_mail_opt3->setDisabled(true);
				$sub_both_opt2->setDisabled(true);
				$sub_both_opt3->setDisabled(true);
			}
		}
		
		$r_opt3->addSubItem($sub_radio_group);
		$this->addOption($r_opt3);
	}


	public function render()
	{
		return parent::render();
	}
	
	/**
	 * @return string
	 */
	public function getContext()
	{
		global $ilCtrl;
		$context = strtolower($ilCtrl->getCmdClass());
		return $context;
	}
}