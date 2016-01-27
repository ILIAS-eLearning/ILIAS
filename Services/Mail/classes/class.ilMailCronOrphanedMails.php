<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Cron/classes/class.ilCronJob.php";
include_once "./Services/Cron/classes/class.ilCronJobResult.php";
/**
 * Delete orphaned mails
 *
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilMailCronOrphanedMails extends ilCronJob
{
	/**
	 * 
	 */
	public function __construct()
	{
		global $lng;
		$lng->loadLanguageModule('mail');
	}	
	
	/**
	 * Get id
	 * @return string
	 */
	public function getId()
	{
		return "mail_orphaned_mails";
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		global $lng;
		return $lng->txt("mail_orphaned_mails");
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		global $lng;
		return $lng->txt("mail_orphaned_mails_desc");
	}
	
	/**
	 * Is to be activated on "installation"
	 * @return boolean
	 */
	public function hasAutoActivation()
	{
		return false;
	}

	/**
	 * Can the schedule be configured?
	 * @return boolean
	 */
	public function hasFlexibleSchedule()
	{
		return false;
	}

	/**
	 * Get schedule type
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}

	/**
	 * Get schedule value
	 * @return int|array
	 */
	public function getDefaultScheduleValue()
	{
		return;
	}

	/**
	 * @return bool
	 */
	public function hasCustomSettings()
	{
		return true;
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		global $ilSetting, $lng;
		
		parent::addCustomSettingsToForm($a_form); 
		
		$threshold = new ilNumberInputGUI($lng->txt('mail_threshold'), 'mail_threshold');
		$threshold->setInfo($lng->txt('mail_threshold_info'));
		$threshold->allowDecimals(false);
		$threshold->setSuffix($lng->txt('days'));
		$threshold->setMinValue(1);
		$threshold->setValue($ilSetting->get('mail_threshold'));

		$a_form->addItem($threshold);
		
		$mail_folder = new ilCheckboxInputGUI($lng->txt('only_inbox_trash'), 'mail_only_inbox_trash');
		$mail_folder->setInfo($lng->txt('only_inbox_trash_info'));
		$mail_folder->setChecked($ilSetting->get('mail_only_inbox_trash'));
		$a_form->addItem($mail_folder);
		
		$notification = new ilNumberInputGUI($lng->txt('mail_notify_orphaned'), 'mail_notify_orphaned');
		$notification->setInfo($lng->txt('mail_notify_orphaned_info'));
		$notification->allowDecimals(false);
		$notification->setSuffix($lng->txt('days'));
		$notification->setMinValue(0);
		
		$mail_threshold = isset($_POST['mail_threshold']) ? (int)$_POST['mail_threshold'] : $ilSetting->get('mail_threshold');
		$maxvalue = $mail_threshold-1;
		$notification->setMaxValue($maxvalue);
		$notification->setValue($ilSetting->get('mail_notify_orphaned'));
		$a_form->addItem($notification);
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 * @return bool
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{	
		global $ilSetting;

		$ilSetting->set('mail_threshold', (int)$a_form->getInput('mail_threshold'));
		$ilSetting->set('mail_only_inbox_trash', (int)$a_form->getInput('mail_only_inbox_trash'));
		$ilSetting->set('mail_notify_orphaned', (int)$a_form->getInput('mail_notify_orphaned'));
		
		if($ilSetting->get('mail_notify_orphaned') == 0)
		{
			global $ilDB;
			//delete all mail_cron_orphaned-table entries! 
			$ilDB->manipulate('DELETE FROM mail_cron_orphaned');
		}
		
		return true;
	}

	/**
	 * Run job
	 * @return ilCronJobResult
	 */
	public function run()
	{
		global $ilSetting; 
		
		$mail_threshold = (int)$ilSetting->get('mail_threshold');

		if( (int)$ilSetting->get('mail_notify_orphaned') >= 1 && $mail_threshold >= 1)
		{
			$this->processNotification();
		}
		
		if((int)$ilSetting->get('last_cronjob_start_ts') && $mail_threshold >= 1)
		{
			$this->processDeletion();
		}

		$result = new ilCronJobResult();
		$status = ilCronJobResult::STATUS_OK;
		$result->setStatus($status);
		return $result;
	}

	private function processNotification()
	{
		include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsNotificationCollector.php';
		$collector = new ilMailCronOrphanedMailsNotificationCollector();

		include_once'./Services/Mail/classes/class.ilMailCronOrphanedMailsNotifier.php';
		$notifier = new ilMailCronOrphanedMailsNotifier($collector);
		$notifier->processNotification();
	}

	private function processDeletion()
	{
		include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsDeletionCollector.php';
		$collector = new ilMailCronOrphanedMailsDeletionCollector();
		
		include_once './Services/Mail/classes/class.ilMailCronOrphanedMailsDeletionProcessor.php';
		$processor = new ilMailCronOrphanedMailsDeletionProcessor($collector);
		$processor->processDeletion();
	}
}