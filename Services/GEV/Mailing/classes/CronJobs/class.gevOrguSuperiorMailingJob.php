<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");


class gevOrguSuperiorMailingJob extends ilCronJob {
	const MAILS_PER_RUN = 100;
	
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
	
	public function run() {
		require_once("Services/GEV/Mailing/classes/class.gevOrguSuperiorMails.php");
		
		global $ilLog, $ilDB;

		$cron_result = new ilCronJobResult();
		$auto_mails = new gevOrguSuperiorMails();


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
			 GROUP BY ua.usr_id
			 HAVING (   last_send < UNIX_TIMESTAMP() - 7 * 24 * 60 * 60
			         OR last_send IS NULL)
			 LIMIT ".self::MAILS_PER_RUN;

		$res = $ilDB->query($sql);

		while($row = $ilDB->fetchAssoc($res)) {
			$superior_id = $row["usr_id"];

			$ilLog->write("gevOrguSuperiorMailingJob::run: Sending mail to $superior_id");

			try {
				$mail->send(array($superior_id));
			}
			catch (Exception $e) {
				$ilLog->write("gevOrguSuperiorMailingJob::run: error when sending mail report_weekly_actions.".$e->getMessage());
			}
			// i'm alive!
			ilCronManager::ping($this->getId());
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}

?>