<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for survey notifications
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup Cron
 */
class ilSurveyCronNotification extends ilCronJob
{		
	public function getId()
	{
		return "survey_notification";
	}
	
	public function getTitle()
	{
		global $lng;
		
		$lng->loadLanguageModule("survey");
		return $lng->txt("survey_reminder_setting");
	}
	
	public function getDescription()
	{
		global $lng;
		
		$lng->loadLanguageModule("survey");
		return $lng->txt("survey_reminder_cron_info");
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
		return true;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}
	
	public function run()
	{
		global $tree;
		
		include_once "Modules/Survey/classes/class.ilObjSurvey.php";
		
		$status = ilCronJobResult::STATUS_NO_ACTION;
		$message = array();
		
		$root = $tree->getNodeData(ROOT_FOLDER_ID);
		foreach($tree->getSubTree($root, false, "svy") as $svy_ref_id)
		{
			$svy = new ilObjSurvey($svy_ref_id);
			if($svy->checkReminder())
			{	
				$message[] = $svy_ref_id;
				$status = ilCronJobResult::STATUS_OK;				
			}
		}
		
		$result = new ilCronJobResult();
		$result->setStatus($status);
		
		if(sizeof($message))
		{
			$result->setMessage("Ref-Ids: ".implode(", ", $message));
			$result->setCode("#".sizeof($message));
		}
		
		return $result;
	}	
}

?>