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
	/**
	 * @var \ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var \ilSetting
	 */
	protected $settings;
	
	/**
	 * @var \ilTemplate
	 */
	protected $tpl;

	/**
	 * ilCronManagerGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->lng      = $DIC->language();
		$this->ctrl     = $DIC->ctrl();
		$this->settings = $DIC->settings();
		$this->tpl      = $DIC->ui()->mainTemplate();

		$this->lng->loadLanguageModule('cron');
	}

	public function executeCommand()
	{	
		$cmd = $this->ctrl->getCmd("render");
		$this->$cmd();

		return true;
	}

	protected function render()
	{
		if($this->settings->get('last_cronjob_start_ts'))
		{
			$tstamp = ilDatePresentation::formatDate(new ilDateTime($this->settings->get('last_cronjob_start_ts'), IL_CAL_UNIX));
		}
		else
		{
			$tstamp = $this->lng->txt('cronjob_last_start_unknown');
		}		
		ilUtil::sendInfo($this->lng->txt('cronjob_last_start').": ".$tstamp);
		
		include_once "Services/Cron/classes/class.ilCronManagerTableGUI.php";
		$tbl = new ilCronManagerTableGUI($this, "render");
		$this->tpl->setContent($tbl->getHTML());
	}
	
	function edit(ilPropertyFormGUI $a_form = null)
	{
		$id = $_REQUEST["jid"];
		if(!$id)
		{
			$this->ctrl->redirect($this, "render");
		}
		
		if(!$a_form)
		{
			$a_form = $this->initEditForm($id);
		}
		
		$this->tpl->setContent($a_form->getHTML());
	}

	/**
	 * @param string $typeLabel
	 * @return string
	 */
	protected function getScheduleValueFormElementName($typeLabel)
	{
		switch ($typeLabel) {
			case 'in_minutes':
				return 'mini';

			case 'in_hours':
				return 'hri';

			case 'in_days':
				return 'dyi';
		}
 	}

	/**
	 * @param string $typeLabel
	 * @return bool
	 */
	protected function hasScheduleValue($typeLabel)
	{
		return in_array($typeLabel, ['in_minutes', 'in_hours', 'in_days']);
	}

	protected function initEditForm($a_job_id)
	{
		$job = ilCronManager::getJobInstanceById($a_job_id);		
		if(!$job)
		{			
			$this->ctrl->redirect($this, "render");
		}

		$this->ctrl->setParameter($this, "jid", $a_job_id);
		
		$data = array_pop(ilCronManager::getCronJobData($job->getId()));				
		
		include_once("Services/Cron/classes/class.ilCronJob.php");
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();	
		$form->setFormAction($this->ctrl->getFormAction($this, "update"));
		$form->setTitle($this->lng->txt("cron_action_edit").': "'.$job->getTitle().'"');		
		
		if($job->hasFlexibleSchedule())
		{
			$type = new ilRadioGroupInputGUI($this->lng->txt("cron_schedule_type"), "type");
			$type->setRequired(true);
			$type->setValue($data["schedule_type"]);

			foreach ($job->getAllScheduleTypes() as $typeLabel => $typeId) {
				if (!in_array($typeId, $job->getValidScheduleTypes())) {
					continue;
				}

				$option = new ilRadioOption(sprintf($this->lng->txt('cron_schedule_' . $typeLabel), 'x'), $typeId);

				$type->addOption($option);

				if ($this->hasScheduleValue($typeLabel)) {
					$scheduleValue = new ilNumberInputGUI(
						$this->lng->txt('cron_schedule_value'),
						's' . $this->getScheduleValueFormElementName($typeLabel)
					);
					$scheduleValue->allowDecimals(false);
					$scheduleValue->setRequired(true);
					$scheduleValue->setSize(5);
					if ($data['schedule_type'] == $typeId) {
						$scheduleValue->setValue($data['schedule_value']);
					}
					$option->addSubItem($scheduleValue);
				}
			}


			$form->addItem($type);
		}
		
		if($job->hasCustomSettings())
		{
			$job->addCustomSettingsToForm($form);		
		}
		
		$form->addCommandButton("update", $this->lng->txt("save"));
		$form->addCommandButton("render", $this->lng->txt("cancel"));
		
		return $form;		
	}
	
	function update()
	{
		$id = $_REQUEST["jid"];
		if(!$id)
		{
			$this->ctrl->redirect($this, "render");
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
					ilUtil::sendSuccess($this->lng->txt("cron_action_edit_success"), true);
					$this->ctrl->redirect($this, "render");
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
		$job_id = $_GET["jid"];
		if($job_id)
		{
			if(ilCronManager::runJobManual($job_id))
			{
				ilUtil::sendSuccess($this->lng->txt("cron_action_run_success"), true);				
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("cron_action_run_fail"), true);	
			}
		}

		$this->ctrl->redirect($this, "render");
	}	
	
	function activate()
	{
		$this->confirm("activate");
	}
	
	function confirmedActivate()
	{
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
			
			ilUtil::sendSuccess($this->lng->txt("cron_action_activate_success"), true);	
		}

		$this->ctrl->redirect($this, "render");
	}
	
	function deactivate()
	{
		$this->confirm("deactivate");
	}
	
	function confirmedDeactivate()
	{
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
			
			ilUtil::sendSuccess($this->lng->txt("cron_action_deactivate_success"), true);	
		}

		$this->ctrl->redirect($this, "render");
	}
	
	function reset()
	{
		$this->confirm("reset");
	}
	
	function confirmedReset()
	{
		$jobs = $this->getMultiActionData();
		if($jobs)
		{
			foreach($jobs as $job)
			{
				if(ilCronManager::isJobActive($job->getId()))
				{
					ilCronManager::resetJob($job);
				}
			}
			ilUtil::sendSuccess($this->lng->txt("cron_action_reset_success"), true);
		}

		$this->ctrl->redirect($this, "render");
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
		$jobs = $this->getMultiActionData();
		if(!$jobs)
		{
			$this->ctrl->redirect($this, "render");
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
				ilUtil::sendFailure($this->lng->txt('cron_no_executable_job_selected'), true);
				$this->ctrl->redirect($this, 'render');
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

			$cgui->setHeaderText(sprintf($this->lng->txt("cron_action_".$a_action."_sure"), 
				$title));

			$this->ctrl->setParameter($this, "jid", $job_id);
		}
		else
		{
			$cgui->setHeaderText($this->lng->txt("cron_action_".$a_action."_sure_multi"));
			
			foreach($jobs as $job_id => $job)
			{
				$cgui->addItem("mjid[]", $job_id, $job->getTitle());
			}			
		}
		
		$cgui->setFormAction($this->ctrl->getFormAction($this, "confirmed".ucfirst($a_action)));
		$cgui->setCancel($this->lng->txt("cancel"), "render");
		$cgui->setConfirm($this->lng->txt("cron_action_".$a_action), "confirmed".ucfirst($a_action));

		$this->tpl->setContent($cgui->getHTML());		
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