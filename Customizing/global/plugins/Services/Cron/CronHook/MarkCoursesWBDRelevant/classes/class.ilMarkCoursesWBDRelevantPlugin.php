<?php
require_once("Services/Cron/classes/class.ilCronHookPlugin.php");

class ilMarkCoursesWBDRelevantPlugin extends ilCronHookPlugin {
	public function getPluginName() {
		return "MarkCoursesWBDRelevant";
	}

	function getCronJobInstances() {
		require_once $this->getDirectory()."/classes/class.ilMarkCoursesWBDRelevantJob.php";
		$job = new ilMarkCoursesWBDRelevantJob();
		return array($job);
	}

	function getCronJobInstance($a_job_id) {
		require_once $this->getDirectory()."/classes/class.ilMarkCoursesWBDRelevantJob.php";
		return new ilMarkCoursesWBDRelevantJob();
	}
}