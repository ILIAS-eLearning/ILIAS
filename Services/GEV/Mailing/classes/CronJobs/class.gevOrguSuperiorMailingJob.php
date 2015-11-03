<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");


class gevOrguSuperiorMailingJob extends ilCronJob {
	const MAILS_PER_RUN = 500;
	protected $start_timestamp = null;
	protected $end_timestamp = null;
	protected $end_date_str = "";

	
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
		return ilCronJob::SCHEDULE_TYPE_IN_HOURS;
	}
	
	public function getDefaultScheduleValue() {
		return 1;
	}

	protected function getStartTimestamp() {
		if($this->start_timestamp === null) {
			if($this->end_date_str == "") {
				$this->createEndTimestamp();
			}

			$start_date = new DateTime($this->end_date_str);
			$start_date->sub(date_interval_create_from_date_string('7 Days'));
			$this->start_timestamp = $start_date->getTimestamp();
		}

		return $this->start_timestamp;
	}

	protected function getEndTimestamp() {
		if($this->end_timestamp === null) {
			$this->createEndTimestamp();
		}

		return $this->end_timestamp;
	}

	protected function createEndTimestamp() {
		$timestamp_today = time();
		$this->end_date_str = date("Y-m-d", $timestamp_today);
		$end_date = new DateTime($this->end_date_str." 23:59:59");

		if(date("l",$timestamp_today) == "Monday") {
			$end_date->sub(date_interval_create_from_date_string('1 Day'));
			$this->end_date_str = $end_date->format("Y-m-d");
		}

		$this->end_timestamp = $end_date->getTimestamp();
	}
	
	public function run() {
		require_once("Services/GEV/Mailing/classes/class.gevOrguSuperiorMails.php");
		
		global $ilLog, $ilDB;

		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		$auto_mails = new gevOrguSuperiorMails();

		if (!$this->shouldRunNow()) {
			return $cron_result;
		}

		$ilLog->write("gevOrguSuperiorMailingJob::run: start sending mails");
		$mail = $auto_mails->getAutoMail("report_weekly_actions");
		
		ilCronManager::ping($this->getId());

		$ilLog->write("gevOrguSuperiorMailingJob::run: Send mail report_weekly_actions.");

		$sql = "SELECT DISTINCT ua.usr_id, MAX(ml.moment) as last_send
			  FROM rbac_ua ua
			  JOIN rbac_fa fa ON ua.rol_id = fa.rol_id
			  JOIN object_data od ON od.obj_id = fa.rol_id
			  JOIN usr_data ud ON ua.usr_id = ud.usr_id
			  LEFT JOIN mail_log ml ON ml.recipient_id = ua.usr_id AND ml.obj_id = ".gevOrguSuperiorMails::MAIL_LOG_ID."
			 WHERE od.title LIKE 'il_orgu_superior_%'
			   AND ud.active = 1
			 GROUP BY ua.usr_id
			 HAVING (   last_send < UNIX_TIMESTAMP() - 7 * 24 * 60 * 60
			         OR last_send IS NULL)";

		$res = $ilDB->query($sql);
		$amount_mails = 0;

		while($row = $ilDB->fetchAssoc($res)) {
			if ($amount_mails >= self::MAILS_PER_RUN) {
				break;
			}
			
			$superior_id = $row["usr_id"];
			require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
			$usr_utils = gevUserUtils::getInstance($superior_id);

			$ilLog->write("gevOrguSuperiorMailingJob::run: Sending mail to $superior_id");

			try {
				$mail->send(array($superior_id));
				$amount_mails++;
			}
			catch (Exception $e) {
				$ilLog->write("gevOrguSuperiorMailingJob::run: error when sending mail report_weekly_actions. ".$e->getMessage());
			}
			// i'm alive!
			ilCronManager::ping($this->getId());
		}

		return $cron_result;
	}
	
	// This job should send mails once in a week on sundays. It also needs to
	// not send all emails in one run to not swamp the mail server.
	public function shouldRunNow() {
		$day_of_week = date("D");
		$hour = date("G");
		
		// Run from 18:00 on sundays...
		if ($day_of_week === "Sun" && $hour >= 18) {
			return true;
		}
		// to 05:00 in the morning
		if ($day_week === "Mon" && $hour < 5) {
			return true;
		}
		
		return false;
	}
}

?>