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
	const MAX_REQUESTS_PER_RUN = 10;
	
	protected $request_db = null;
	protected $auto_mails = null;
	
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
		// As we will be running this in a custom cron script, the
		// regular script does not need to run this very often.
		return ilCronJob::SCHEDULE_TYPE_YEARLY;
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
	
	protected function log($msg) {
		$log = $this->getLog();
		$log->write("gevDecentralTrainingCreationJob: $msg");
	}
	
	public function run() {
		$request_db = $this->getRequestDB();
		$counter = 0;
		
		while($request = $request_db->nextOpenRequest()) {
			if ($counter >= self::MAX_REQUESTS_PER_RUN) {
				break;
			}

			// Create Training
			try {
				$this->log("Running request: ".$request->requestId());
				$request->run();
				$this->log("Finished request: ".$request->requestId());
				$mail = "success";
			}
			catch (Exception $e) {
				$this->log("Exception when running: ".$request->requestId()."\n"
						  ."--------------------------------------\n"
						  .$e
						  ."--------------------------------------\n");

				if ($e instanceof ilHistorizingException) {
					$this->log("Retrying request...");
					$mail = false;
				}
				else {
					$request->abort();
					$this->log("Aborted request: ".$request->requestId());
					$mail = "failure";
				}
			}
			
			if ($mail) {
				// Send Mail
				try {
					$this->sendAutoMail($mail, $request);
				}
				catch(Exception $e) {
					$this->log("Exception when sending $mail mail: ".$request->requestId()."\n"
							  ."--------------------------------------\n"
							  .$e
							  ."--------------------------------------\n");
				}
			}

			$counter++;
			$this->ping();
		}
		
		return $this->ok();
	}
	
	protected function sendAutoMail($id, gevDecentralTrainingCreationRequest $request) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingAutoMails.php");
		$auto_mails = new gevDecentralTrainingsAutoMails($request);
		$auto_mails->send($id);
	}
}
