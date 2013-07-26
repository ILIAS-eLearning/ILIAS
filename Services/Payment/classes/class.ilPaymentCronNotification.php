<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Payment notifications
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilPaymentCronNotification extends ilCronJob
{
	public function getId()
	{
		return "pay_notification";
	}
	
	public function getTitle()
	{
		global $lng;
			
		return $lng->txt("payment_notification");
	}
	
	public function getDescription()
	{
		global $lng;
			
		return $lng->txt("payment_notification_desc");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}
	
	public function hasCustomSettings() 
	{
		return true;
	}

	public function run()
	{						
		require_once 'Services/Payment/classes/class.ilPaymentNotification.php';
		$msn = new ilPaymentNotification();
		$msn->send();

		include_once './Services/Payment/classes/class.ilUserDefinedInvoiceNumber.php';
		if(ilUserDefinedInvoiceNumber::_isUDInvoiceNumberActive())
		{			
			$msn = new ilUserDefinedInvoiceNumber();
			$msn->cronCheck();
		}
	
		$result = new ilCronJobResult();
		$result->setStatus(ilCronJobResult::STATUS_OK);		
		return $result;
	}
	
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		global $lng, $ilSetting;
		
		$num_days = new ilNumberInputGUI($lng->txt('payment_notification_days'),'payment_notification_days');
		$num_days->setSize(3);
		$num_days->setMinValue(0);
		$num_days->setMaxValue(120);
		$num_days->setRequired(true);
		$num_days->setValue($ilSetting->get('payment_notification_days'));
		$num_days->setInfo($lng->txt('payment_notification_days_desc'));
		$a_form->addItem($num_days);
	}
	
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{			
		global $ilSetting;
		
		$ilSetting->set('payment_notification_days', $_POST['payment_notification_days']);	
		
		// invoice_number_reset_period is not saved ?!
		// see: ilObjSystemFolderGUI::saveCronJobsObject() [<= 4.3.x]
		
		// see also
		// - ilObjPaymentSettings::generalSettingsObject()
		// - ilObjPaymentSettings::saveInvoiceNumberObject()
			
		return true;
	}
	
	public function activationWasToggled($a_currently_active)
	{		
		global $ilSetting;
		
		// propagate cron-job setting to object setting
		$ilSetting->set('payment_notification', (bool)$a_currently_active);
	}
}