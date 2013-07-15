<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * List all active cron jobs
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
class ilCronManagerTableGUI extends ilTable2GUI
{	
	/**
	 * Constructor
	 *
	 * @param ilObject $a_parent_obj
	 * @param string $a_parent_cmd
	 */
	public function  __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($this->lng->txt("cron_job_id"), "title");
		$this->addColumn($this->lng->txt("cron_component"), "component");
		$this->addColumn($this->lng->txt("cron_schedule"), "schedule");
		$this->addColumn($this->lng->txt("cron_status"), "status");
		$this->addColumn($this->lng->txt("cron_status_info"), "");
		$this->addColumn($this->lng->txt("cron_result"), "result");
		$this->addColumn($this->lng->txt("cron_result_info"), "");		
		$this->addColumn($this->lng->txt("cron_last_run"), "last_run");			
		$this->addColumn($this->lng->txt("actions"), "");
		
		$this->setTitle($this->lng->txt("cron_jobs"));
		$this->setDefaultOrderField("title");
						
		$this->setRowTemplate("tpl.cron_job_row.html", "Services/Cron");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
				
		$this->getItems();
	}

	protected function getItems()
	{			
		global $lng;
		
		include_once "Services/User/classes/class.ilUserUtil.php";
		include_once "Services/Cron/classes/class.ilCronJobResult.php";
		
		$data = ilCronManager::getCronJobData();
		foreach($data as $idx => $item)
		{
			$job = ilCronManager::getJobInstance($item["job_id"],
				$item["component"], $item["class"], $item["path"]);
			
			$data[$idx]["title"] = $job->getTitle();
			$data[$idx]["description"] = $job->getDescription();
			$data[$idx]["has_settings"] = $job->hasCustomSettings();			
			
			if(!$data[$idx]["title"])
			{
				$data[$idx]["title"] = $item["job_id"];
			}
			
			// schedule			
			if(!$job->hasFlexibleSchedule())
			{		
				// schedule type changed
				if($item["schedule_type"])
				{
					ilCronManager::updateJobSchedule($job, null, null);
				}
				
				$item["schedule_type"] = $job->getDefaultScheduleType();
				$item["schedule_value"] = $job->getDefaultScheduleValue();
				$data[$idx]["editable_schedule"] = false;
			}
			else
			{
				// schedule type changed
				if(!$item["schedule_type"])
				{
					$item["schedule_type"] = $job->getDefaultScheduleType();
					$item["schedule_value"] = $job->getDefaultScheduleValue();
					ilCronManager::updateJobSchedule($job, $item["schedule_type"], 
						$item["schedule_value"]);
				}
				
				$data[$idx]["editable_schedule"] = true;
			}
			
			switch($item["schedule_type"])
			{
				case ilCronJob::SCHEDULE_TYPE_DAILY:
					$schedule = $lng->txt("cron_schedule_daily");
					break;
				
				case ilCronJob::SCHEDULE_TYPE_WEEKLY:
					$schedule = $lng->txt("cron_schedule_weekly");
					break;
				
				case ilCronJob::SCHEDULE_TYPE_MONTHLY:
					$schedule = $lng->txt("cron_schedule_monthly");
					break;
				
				case ilCronJob::SCHEDULE_TYPE_QUARTERLY:
					$schedule = $lng->txt("cron_schedule_quarterly");
					break;
				
				case ilCronJob::SCHEDULE_TYPE_YEARLY:
					$schedule = $lng->txt("cron_schedule_yearly");
					break;
				
				case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
					$schedule = sprintf($lng->txt("cron_schedule_in_minutes"), $item["schedule_value"]);
					break;
				
				case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
					$schedule = sprintf($lng->txt("cron_schedule_in_hours"), $item["schedule_value"]);
					break;
				
				case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
					$schedule = sprintf($lng->txt("cron_schedule_in_days"), $item["schedule_value"]);
					break;
			}			
			$data[$idx]["schedule"] = $schedule;
						
			// status
			if($item["job_status"])
			{
				$data[$idx]["status"] = $lng->txt("cron_status_active");
			}
			else
			{
				$data[$idx]["status"] = $lng->txt("cron_status_inactive");
			}					
			
			$status_info = array();
			if($item["job_status_ts"])
			{				
				$status_info[] = ilDatePresentation::formatDate(new ilDateTime($item["job_status_ts"], IL_CAL_UNIX));
			}
			if(!$item["job_status_type"])
			{
				$status_info[] = $lng->txt("cron_changed_by_crontab");
			}
			else
			{
				$status_info[] = ilUserUtil::getNamePresentation($item["job_status_user_id"]);
			}
			$data[$idx]["status_info"] = implode("<br />", $status_info); 
			
			// result
			$result = "-";
			if($item["job_result_status"])
			{
				switch($item["job_result_status"])
				{
					case ilCronJobResult::STATUS_INVALID_CONFIGURATION:
						$result = $lng->txt("cron_result_status_invalid_configuration");
						break;
					
					case ilCronJobResult::STATUS_NO_ACTION:
						$result = $lng->txt("cron_result_status_no_action");
						break;
					
					case ilCronJobResult::STATUS_OK:
						$result = $lng->txt("cron_result_status_ok");
						break;
					
					case ilCronJobResult::STATUS_CRASHED:
						$result = $lng->txt("cron_result_status_crashed");
						break;
					
					case ilCronJobResult::STATUS_RESET:
						$result = $lng->txt("cron_result_status_reset");
						break;
				}			
			}
			$data[$idx]["result"] = $result;
			
			$result_info = array();			
			if($item["job_result_dur"])
			{
				$result_info[] = ($item["job_result_dur"]/1000)." sec";
			}
			if($item["job_result_message"])
			{
				$result_info[] = $item["job_result_message"];
			}
			if($item["job_result_code"])
			{
				$result_info[] = $item["job_result_code"];
			}
			if(!$item["job_result_type"])
			{
				$result_info[] = $lng->txt("cron_changed_by_crontab");
			}
			else
			{
				$result_info[] = ilUserUtil::getNamePresentation($item["job_result_user_id"]);
			}
			$data[$idx]["result_info"] = implode("<br />", $result_info); 
			
			if($item["running_ts"])
			{
				$data[$idx]["last_run"] = strtotime("+1year", $item["running_ts"]);				
			}
			else if($item["job_result_ts"])
			{				
				$data[$idx]["last_run"] = $item["job_result_ts"];
			}
			else
			{
				$data[$idx]["last_run"] = null;
			}			
		}
		
		$this->setData($data);
	}

	protected function fillRow($a_set)
	{		
		global $ilCtrl, $lng;
		
		$this->tpl->setVariable("VAL_ID", $a_set["title"]);
		
		if($a_set["description"])
		{
			$this->tpl->setVariable("VAL_DESC", $a_set["description"]);
		}
		
		$this->tpl->setVariable("VAL_COMPONENT", $a_set["component"]);
		$this->tpl->setVariable("VAL_SCHEDULE", $a_set["schedule"]);
		$this->tpl->setVariable("VAL_STATUS", $a_set["status"]);
		$this->tpl->setVariable("VAL_STATUS_INFO", $a_set["status_info"]);		
		$this->tpl->setVariable("VAL_RESULT", $a_set["result"]);	
		$this->tpl->setVariable("VAL_RESULT_INFO", $a_set["result_info"]);		
		if($a_set["last_run"] > time())
		{
			$a_set["last_run"] = $lng->txt("cron_running_since")." ".
				ilDatePresentation::formatDate(new ilDateTime($a_set["running_ts"], IL_CAL_UNIX));
			
			// job has pinged 
			if($a_set["alive_ts"] != $a_set["running_ts"])
			{
				$a_set["last_run"] .= "<br />(Ping: ".
					ilDatePresentation::formatDate(new ilDateTime($a_set["alive_ts"], IL_CAL_UNIX)).")";
			}
		}
		else if($a_set["last_run"])
		{
			$a_set["last_run"] = ilDatePresentation::formatDate(new ilDateTime($a_set["last_run"], IL_CAL_UNIX));
		}		
		$this->tpl->setVariable("VAL_LAST_RUN", $a_set["last_run"] ? $a_set["last_run"] : "-");	
		
		
		// actions
		
		$actions = array();
		
		if(!$a_set["running_ts"])
		{
			// reset
			if($a_set["job_result_status"] == ilCronJobResult::STATUS_CRASHED)
			{
				$actions[] = "reset";
			}
			// activate
			else if(!$a_set["job_status"])
			{
				$actions[] = "activate";
			}
			// deactivate
			else 
			{
				$actions[] = "run";
				$actions[] = "deactivate";
			}
			// edit (schedule)
			if($a_set["editable_schedule"] || $a_set["has_settings"])
			{
				$actions[] = "edit";
			}		
			
			$ilCtrl->setParameter($this->getParentObject(), "jid", $a_set["job_id"]);
			
			foreach($actions as $action)
			{
				$this->tpl->setCurrentBlock("action_bl");
				$this->tpl->setVariable("URL_ACTION", 
					$ilCtrl->getLinkTarget($this->getParentObject(), $action));	
				$this->tpl->setVariable("TXT_ACTION", $lng->txt("cron_action_".$action));
				$this->tpl->parseCurrentBlock();	
			}
			
			$ilCtrl->setParameter($this->getParentObject(), "jid", "");
		}
	}
}

?>