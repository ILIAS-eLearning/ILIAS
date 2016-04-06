<?php
/**
 * A cron-hook plugin to cancel trainings with end-date < today and having no booked participants.
 * Such trainings can not be finalized and thus need to be cancelled, in order to make them 
 * invisible in various reports.
 */

require_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
 
class ilGEVCancelPastEmptyTrainingsPlugin extends ilCronHookPlugin {
	function getPluginName() {
		return "GEVCancelPastEmptyTrainings";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.gevCancelPastEmptyTrainingsJob.php";
		$job = new gevCancelPastEmptyTrainingsJob();
		return array($job);
	}

	function getCronJobInstance($a_job_id) {                
		require_once $this->getDirectory()."/classes/class.gevCancelPastEmptyTrainingsJob.php";
		return new gevCancelPastEmptyTrainingsJob();
	}
}