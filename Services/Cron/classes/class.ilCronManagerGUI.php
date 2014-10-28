<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronManager.php";

/**
 * Class ilCronManagerGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
 *
 * @ilCtrl_Calls ilCronManagerGUI:
 * @ingroup ServicesCron
 */
class ilCronManagerGUI 
{
	function executeCommand()
	{
		global $ilCtrl, $lng;
		
		$lng->loadLanguageModule("cron");

		$cmd = $ilCtrl->getCmd("render");		
		$this->$cmd();
		
		return true;
	}
	
	function render()
	{
		global $tpl, $ilSetting, $lng;
		
		if($ilSetting->get('last_cronjob_start_ts'))
		{
			$tstamp = ilDatePresentation::formatDate(new ilDateTime($ilSetting->get('last_cronjob_start_ts'), IL_CAL_UNIX));
		}
		else
		{
			$tstamp = $lng->txt('cronjob_last_start_unknown');
		}		
		ilUtil::sendInfo($lng->txt('cronjob_last_start').": ".$tstamp);
		
		include_once "Services/Cron/classes/class.ilCronManagerTableGUI.php";
		$tbl = new ilCronManagerTableGUI($this, "render");
		$tpl->setContent($tbl->getHTML());		
	}
	
	function edit(ilPropertyFormGUI $a_form = null)
	{
		global $ilCtrl, $tpl;
		
		$id = $_REQUEST["jid"];
		if(!$id)
		{
			$ilCtrl->redirect($this, "render");
		}
		
		if(!$a_form)
		{
			$a_form = $this->initEditForm($id);
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function initEditForm($a_job_id)
	{
		global $ilCtrl, $lng;
		
		$job = ilCronManager::getJobInstanceById($a_job_id);		
		if(!$job)
		{			
			$ilCtrl->redirect($this, "render");
		}
		
		$ilCtrl->setParameter($this, "jid", $a_job_id);
		
		$data = array_pop(ilCronManager::getCronJobData($job->getId()));				
		
		include_once("Services/Cron/classes/class.ilCronJob.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();	
		$form->setFormAction($ilCtrl->getFormAction($this, "update"));
		$form->setTitle($lng->txt("cron_action_edit").': "'.$job->getTitle().'"');		
		
		if($job->hasFlexibleSchedule())
		{
			$type = new ilRadioGroupInputGUI($lng->txt("cron_schedule_type"), "type");
			$type->setRequired(true);
			$type->setValue($data["schedule_type"]);
			$type->addOption(new ilRadioOption($lng->txt("cron_schedule_daily"), ilCronJob::SCHEDULE_TYPE_DAILY));
			$type->addOption(new ilRadioOption($lng->txt("cron_schedule_weekly"), ilCronJob::SCHEDULE_TYPE_WEEKLY));
			$type->addOption(new ilRadioOption($lng->txt("cron_schedule_monthly"), ilCronJob::SCHEDULE_TYPE_MONTHLY));
			$type->addOption(new ilRadioOption($lng->txt("cron_schedule_quarterly"), ilCronJob::SCHEDULE_TYPE_QUARTERLY));
			$type->addOption(new ilRadioOption($lng->txt("cron_schedule_yearly"), ilCronJob::SCHEDULE_TYPE_YEARLY));

			$min = new ilRadioOption(sprintf($lng->txt("cron_schedule_in_minutes"), "x"), 
				ilCronJob::SCHEDULE_TYPE_IN_MINUTES);
			$mini = new ilNumberInputGUI($lng->txt("cron_schedule_value"), "smini");
			$mini->setRequired(true);
			$mini->setSize(5);
			if($data["schedule_type"] == ilCronJob::SCHEDULE_TYPE_IN_MINUTES)
			{
				$mini->setValue($data["schedule_value"]);
			}
			$min->addSubItem($mini);
			$type->addOption($min);

			$hr = new ilRadioOption(sprintf($lng->txt("cron_schedule_in_hours"), "x"), 
				ilCronJob::SCHEDULE_TYPE_IN_HOURS);
			$hri = new ilNumberInputGUI($lng->txt("cron_schedule_value"), "shri");
			$hri->setRequired(true);
			$hri->setSize(5);
			if($data["schedule_type"] == ilCronJob::SCHEDULE_TYPE_IN_HOURS)
			{
				$hri->setValue($data["schedule_value"]);
			}
			$hr->addSubItem($hri);
			$type->addOption($hr);

			$dy = new ilRadioOption(sprintf($lng->txt("cron_schedule_in_days"), "x"), 
				ilCronJob::SCHEDULE_TYPE_IN_DAYS);
			$dyi = new ilNumberInputGUI($lng->txt("cron_schedule_value"), "sdyi");
			$dyi->setRequired(true);
			$dyi->setSize(5);
			if($data["schedule_type"] == ilCronJob::SCHEDULE_TYPE_IN_DAYS)
			{
				$dyi->setValue($data["schedule_value"]);
			}
			$dy->addSubItem($dyi);		
			$type->addOption($dy);

			$form->addItem($type);
		}
		
		if($job->hasCustomSettings())
		{
			$job->addCustomSettingsToForm($form);		
		}
		
		$form->addCommandButton("update", $lng->txt("save"));
		$form->addCommandButton("render", $lng->txt("cancel"));
		
		return $form;		
	}
	
	function update()
	{
		global $ilCtrl, $lng;
		
		$id = $_REQUEST["jid"];
		if(!$id)
		{
			$ilCtrl->redirect($this, "render");
		}
		
		$form = $this->initEditForm($id);
		if($form->checkInput())
		{			
			$job = ilCronManager::getJobInstanceById($id);					
			if($job)
			{
				$valid = true;
				if($job->hasCustomSettings() &&
					!$job->saveCustomSettings($form))
				{
					$valid = false;
				}
				
				if($valid && $job->hasFlexibleSchedule())
				{
					$type = $form->getInput("type");
					switch($type)
					{
						case ilCronJob::SCHEDULE_TYPE_IN_MINUTES:
							$value = $form->getInput("smini");
							break;

						case ilCronJob::SCHEDULE_TYPE_IN_HOURS:
							$value = $form->getInput("shri");
							break;

						case ilCronJob::SCHEDULE_TYPE_IN_DAYS:
							$value = $form->getInput("sdyi");
							break;

						default:
							$value = null;					
					}

					ilCronManager::updateJobSchedule($job, $type, $value);
				}
				if($valid)
				{
					ilUtil::sendSuccess($lng->txt("cron_action_edit_success"), true);
					$ilCtrl->redirect($this, "render");
				}
			}
		}
		
		$form->setValuesByPost();
		$this->edit($form);
	}
		
	function run()
	{
		$this->confirm("run");
	}
	
	function confirmedRun()
	{
		global $ilCtrl, $lng;
		
		$job_id = $_GET["jid"];
		if($job_id)
		{
			if(ilCronManager::runJobManual($job_id))
			{
				ilUtil::sendSuccess($lng->txt("cron_action_run_success"), true);				
			}
			else
			{
				ilUtil::sendFailure($lng->txt("cron_action_run_fail"), true);	
			}
		}		
		
		$ilCtrl->redirect($this, "render");
	}	
	
	function activate()
	{
		$this->confirm("activate");
	}
	
	function confirmedActivate()
	{
		global $ilCtrl, $lng;
		
		$jobs = $this->getMultiActionData();
		if($jobs)
		{
			foreach($jobs as $job)
			{			
				if(ilCronManager::isJobInactive($job->getId()))
				{
					ilCronManager::resetJob($job);
					ilCronManager::activateJob($job, true);			
				}
			}
			
			ilUtil::sendSuccess($lng->txt("cron_action_activate_success"), true);	
		}		
			
		$ilCtrl->redirect($this, "render");
	}
	
	function deactivate()
	{
		$this->confirm("deactivate");
	}
	
	function confirmedDeactivate()
	{
		global $ilCtrl, $lng;
		
		$jobs = $this->getMultiActionData();
		if($jobs)
		{
			foreach($jobs as $job)
			{		
				if(ilCronManager::isJobActive($job->getId()))
				{					
					ilCronManager::deactivateJob($job, true);				
				}
			}
			
			ilUtil::sendSuccess($lng->txt("cron_action_deactivate_success"), true);	
		}	
		
		$ilCtrl->redirect($this, "render");
	}
	
	function reset()
	{
		$this->confirm("reset");
	}
	
	function confirmedReset()
	{
		global $ilCtrl, $lng;
		
		$job_id = $_GET["jid"];
		if($job_id)
		{
			$job = ilCronManager::getJobInstanceById($job_id);
			if($job)
			{
				ilCronManager::resetJob($job);
				
				ilUtil::sendSuccess($lng->txt("cron_action_reset_success"), true);	
			}
		}		
		
		$ilCtrl->redirect($this, "render");
	}
	
	protected function getMultiActionData()
	{
		$res = array();
		
		if($_REQUEST["jid"])
		{
			$job_id = trim($_REQUEST["jid"]);
			$job = ilCronManager::getJobInstanceById($job_id);
			if($job)
			{
				$res[$job_id] = $job;
			}
		}
		else if(is_array($_REQUEST["mjid"]))
		{
			foreach($_REQUEST["mjid"] as $job_id)
			{
				$job = ilCronManager::getJobInstanceById($job_id);
				if($job)
				{
					$res[$job_id] = $job;
				}
			}			
		}
	
		return $res;		
	}
	
	protected function confirm($a_action)
	{
		global $ilCtrl, $tpl, $lng;
		
		$jobs = $this->getMultiActionData();
		if(!$jobs)
		{
			$ilCtrl->redirect($this, "render");
		}

		if('run' == $a_action)
		{
			// Filter jobs which are not indented to be executed manually
			$jobs = array_filter($jobs, function ($job) {
				/**
				 * @var $job ilCronJob
				 */
				return $job->isManuallyExecutable();
			});

			if(0 == count($jobs))
			{
				ilUtil::sendFailure($lng->txt('cron_no_executable_job_selected'), true);
				$ilCtrl->redirect($this, 'render');
			}
		}

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		
		if(sizeof($jobs) == 1)
		{
			$job_id = array_pop(array_keys($jobs));
			$job = array_pop($jobs);				
			$title = $job->getTitle();
			if(!$title)
			{
				$title = preg_replace("[^A-Za-z0-9_\-]", "", $job->getId());
			}

			$cgui->setHeaderText(sprintf($lng->txt("cron_action_".$a_action."_sure"), 
				$title));

			$ilCtrl->setParameter($this, "jid", $job_id);
		}
		else
		{
			$cgui->setHeaderText($lng->txt("cron_action_".$a_action."_sure_multi"));
			
			foreach($jobs as $job_id => $job)
			{
				$cgui->addItem("mjid[]", $job_id, $job->getTitle());
			}			
		}
		
		$cgui->setFormAction($ilCtrl->getFormAction($this, "confirmed".ucfirst($a_action)));
		$cgui->setCancel($lng->txt("cancel"), "render");
		$cgui->setConfirm($lng->txt("cron_action_".$a_action), "confirmed".ucfirst($a_action));

		$tpl->setContent($cgui->getHTML());		
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{	
		$fields = array();
		
		$data = ilCronManager::getCronJobData();
		foreach($data as $item)
		{
			$job = ilCronManager::getJobInstance($item["job_id"],
				$item["component"], $item["class"], $item["path"]);
			
			if(method_exists($job, "addToExternalSettingsForm"))
			{
				$job->addToExternalSettingsForm($a_form_id, $fields, $item["job_status"]);													
			}
		}
		
		if(sizeof($fields))
		{
			return array("cron_jobs"=>array("jumpToCronJobs", $fields));
		}
	}
}

?>