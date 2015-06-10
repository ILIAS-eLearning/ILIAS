<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");


class gevOrguSuperiorMailingJob extends ilCronJob {
	public function getId() {
		return "gev_orgu_superior_mailing";
	}
	
	public function getTitle() {
		return "Mailing für Führungskräfte";
	}

	public function hasAutoActivation() {
		return true;
	}
	
	public function hasFlexibleSchedule() {
		return false;
	}
	
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_WEEKLY;
	}
	
	public function getDefaultScheduleValue() {
		return 1;
	}
	
	public function run() {
		require_once("Services/GEV/Mailing/classes/class.gevOrguSuperiorMails.php");
		
		global $ilLog, $ilDB;

		$cron_result = new ilCronJobResult();
		$auto_mails = new gevOrguSuperiorMails();


		$ilLog->write("gevOrguSuperiorMailingJob::run: start sending mails");
		$mail = $auto_mails->getAutoMail("report_weekly_actions");
		
		ilCronManager::ping($this->getId());

		$ilLog->write("gevOrguSuperiorMailingJob::run: Send mail report_weekly_actions.");

		try {
			$mail->send();
		}
		catch (Exception $e) {
			$ilLog->write("gevOrguSuperiorMailingJob::run: error when sending mail report_weekly_actions.");
		}
		// i'm alive!
		ilCronManager::ping($this->getId());

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}

?>