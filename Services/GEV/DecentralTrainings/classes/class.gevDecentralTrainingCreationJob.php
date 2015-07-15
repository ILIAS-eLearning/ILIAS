<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */#

/**
* Cronjob that runs the request for decentral training creation.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

class gevDecentralTrainingCreationJob extends ilCronJob {
	protected $request_db = null;
	
	public function getId() {
		return "dct_creation";
	}
	
	public function getTitle() {
		return "Erzeugung von dezentralen Trainings";
	}

	public function hasAutoActivation() {
		return true;
	}
	
	public function hasFlexibleSchedule() {
		return false;
	}
	
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_IN_MINUTES;
	}
	
	public function getDefaultScheduleValue() {
		return 1;
	}
	
	protected function getRequestDB() {
		if ($this->request_db === null) {
			require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
			$this->request_db = gevDecentralTrainingUtils::getInstance()->getCreationRequestDB();
		}
		return $this->request_db;
	}
	
	protected function getLog() {
		global $ilLog;
		return $ilLog;
	}
	
	protected function ping() {
		ilCronManager::ping($this->getId());
	}
	
	protected function ok() {
		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
	
	public function log($msg) {
		$log = $this->getLog();
		$log->write("gevDecentralTrainingCreationJob: $msg");
	}
	public function run() {
		$request_db = $this->getRequestDB();
		
		while($request = $request_db->getNextOpenRequest()) {
			try {
				$this->log("Running request: ".$request->requestId());
				$request->run();
				$this->log("Finished request: ".$request->requestId());
			}
			catch (Exception $e) {
				$this->log("Exception when running: ".$request->requestId()."\n"
						  ."--------------------------------------\n"
						  .$e
						  ."--------------------------------------\n");
				$request->abort();
				$this->log("Aborted request: ".$request->requestId());
			}
			$this->ping();
		}
		
		return $this->ok();
	}
}
