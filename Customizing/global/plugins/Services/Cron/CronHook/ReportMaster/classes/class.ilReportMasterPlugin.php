<?php
/**
 * A cron-hook plugin to cancel trainings with end-date < today and having no booked participants.
 * Such trainings can not be finalized and thus need to be cancelled, in order to make them 
 * invisible in various reports.
 */

require_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
 
class ilReportMasterPlugin extends ilCronHookPlugin {
	function getPluginName() {
		return "ReportMaster";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.ReportMasterJob.php";
		$job = new ReportMasterJob();
		return array($job);
	}

	function getCronJobInstance($a_job_id) {                
		require_once $this->getDirectory()."/classes/class.ReportMasterJob.php";
		return new ReportMasterJob();
	}
}