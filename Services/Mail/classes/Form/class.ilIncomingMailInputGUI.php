<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilMailOptions.php';
require_once 'Services/Form/classes/class.ilRadioGroupInputGUI.php';
require_once 'Services/Form/classes/class.ilRadioOption.php';

/**
 * Class ilIncomingMailInputGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilIncomingMailInputGUI extends ilRadioGroupInputGUI
{
	/**
	 * @var bool
	 */
	protected $freeOptionChoice = true;

	/**
	 * ilIncomingMailInputGUI constructor.
	 * @param string $title
	 * @param string $post_var
	 * @param bool   $freeOptionChoice
	 */
	public function __construct($title = '', $post_var = '', $freeOptionChoice = true)
	{
		parent::__construct($title, $post_var);
		$this->setFreeOptionChoice($freeOptionChoice);
		$this->addSubOptions();
	}

	/**
	 * @return bool
	 */
	public function isFreeOptionChoice()
	{
		return $this->freeOptionChoice;
	}

	/**
	 * @param bool $freeOptionChoice
	 */
	public function setFreeOptionChoice($freeOptionChoice)
	{
		$this->freeOptionChoice = $freeOptionChoice;
	}

	/**
	 * 
	 */
	private function addSubOptions()
	{
		global $DIC;
		
		$incomingLocal    = new ilRadioOption($DIC->language()->txt('mail_incoming_local'), ilMailOptions::INCOMING_LOCAL);
		$incomingExternal  = new ilRadioOption($DIC->language()->txt('mail_incoming_smtp'), ilMailOptions::INCOMING_EMAIL);
		$incomingBoth      = new ilRadioOption($DIC->language()->txt('mail_incoming_both'), ilMailOptions::INCOMING_BOTH);

		$this->addOption($incomingLocal);
		$this->addOption($incomingExternal);
		$this->addOption($incomingBoth);

		$incomingExternalAddressChoice = new ilRadioGroupInputGUI('', 'mail_address_option');
		$sub_mail_opt1   = new ilRadioOption($DIC->language()->txt('mail_first_email'), ilMailOptions::FIRST_EMAIL);
		$incomingExternalAddressChoice->addOption($sub_mail_opt1);

		$sub_mail_opt2 = new ilRadioOption($DIC->language()->txt('mail_second_email'), ilMailOptions::SECOND_EMAIL);
		$incomingExternalAddressChoice->addOption($sub_mail_opt2);

		$sub_mail_opt3 = new ilRadioOption($DIC->language()->txt('mail_both_email'), ilMailOptions::BOTH_EMAIL);
		$incomingExternalAddressChoice->addOption($sub_mail_opt3);

		$incomingBothAddressChoice = new ilRadioGroupInputGUI('', 'mail_address_option_both');
		$sub_both_opt1   = new ilRadioOption($DIC->language()->txt('mail_first_email'), ilMailOptions::FIRST_EMAIL);
		$incomingBothAddressChoice->addOption($sub_both_opt1);

		$sub_both_opt2 = new ilRadioOption($DIC->language()->txt('mail_second_email'), ilMailOptions::SECOND_EMAIL);
		$incomingBothAddressChoice->addOption($sub_both_opt2);

		$sub_both_opt3 = new ilRadioOption($DIC->language()->txt('mail_both_email'), ilMailOptions::BOTH_EMAIL);
		$incomingBothAddressChoice->addOption($sub_both_opt3);

		if(!$this->isFreeOptionChoice())
		{
			if(
				!strlen(ilObjUser::_lookupEmail($DIC->user()->getId())) ||
				$DIC->settings()->get('usr_settings_disable_mail_incoming_mail') == '1'
			)
			{
				$this->setDisabled(true);
			}

			if(!strlen($DIC->user()->getEmail()))
			{
				$sub_mail_opt1->setDisabled(true);
				$sub_mail_opt1->setInfo($DIC->language()->txt('first_email_missing_info'));
				$sub_mail_opt3->setDisabled(true);
				$sub_mail_opt3->setInfo($DIC->language()->txt('first_email_missing_info'));
				$sub_both_opt1->setDisabled(true);
				$sub_both_opt1->setInfo($DIC->language()->txt('first_email_missing_info'));
				$sub_both_opt3->setDisabled(true);
				$sub_both_opt3->setInfo($DIC->language()->txt('first_email_missing_info'));
			}

			if(!strlen($DIC->user()->getSecondEmail()))
			{
				$sub_mail_opt2->setDisabled(true);
				$sub_mail_opt2->setInfo($DIC->language()->txt('second_email_missing_info'));
				$sub_mail_opt3->setDisabled(true);
				$sub_mail_opt3->setInfo($DIC->language()->txt('second_email_missing_info'));
				$sub_both_opt2->setDisabled(true);
				$sub_both_opt2->setInfo($DIC->language()->txt('second_email_missing_info'));
				$sub_both_opt3->setDisabled(true);
				$sub_both_opt3->setInfo($DIC->language()->txt('second_email_missing_info'));
			}
		}

		$incomingExternal->addSubItem($incomingExternalAddressChoice);
		$incomingBoth->addSubItem($incomingBothAddressChoice);
	}
}