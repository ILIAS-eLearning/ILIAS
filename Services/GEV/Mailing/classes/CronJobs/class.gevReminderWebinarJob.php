<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");

class gevReminderWebinarJob extends ilCronJob {
	const VC_NAME_AT_AND_T = "AT&T Connect";

	public function getId() {
		return "gev_mail_reminder_webinar";
	}
	
	public function getTitle() {
		return "Versendet eine Erinnerung eine Stunde bevor das Webinar beginnt.";
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
	
	public function run() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Mailing/classes/class.gevWebinarAutoMails.php");
		
		global $ilLog, $ilDB;
		
		$cron_result = new ilCronJobResult();
		$cron_result->setStatus(ilCronJobResult::STATUS_OK);

		$ilLog->write("gevReminderWebinarJob::run: collect crs_ids.");

		$today_date = date("Y-m-d");
		$today_time = date("H:i:00");

		$query = "SELECT crs.crs_id, ml.id\n"
				." FROM hist_course crs\n"
				." LEFT JOIN mail_log ml ON crs.crs_id = ml.obj_id\n"
				."       AND ml.mail_id = ".$ilDB->quote("reminder_webinare","text")."\n"
				." WHERE crs.crs_id > 0\n"
				."       AND crs.hist_historic = 0\n"
				."       AND crs.begin_date = ".$ilDB->quote($today_date,"text")."\n"
				."       AND crs.type = ".$ilDB->quote("Webinar","text")."\n"
				." HAVING ml.id IS NULL";

		$res = $ilDB->query($query);
		ilCronManager::ping($this->getId());

		while($row = $ilDB->fetchAssoc($res)) {
			$crs_id = $row["crs_id"];
			$crs_utils = gevCourseUtils::getInstance($crs_id);

			if($crs_utils->getVirtualClassType() == self::VC_NAME_AT_AND_T) {
				$ilLog->write("gevReminderWebinarJob::run: webinar (".$crs_id.") has vc ".self::VC_NAME_AT_AND_T.". No mail will be send.");
				continue;
			}

			$auto_mails = new gevWebinarAutoMails($crs_id);
			$mail = $auto_mails->getAutoMail("reminder_webinare");
			ilCronManager::ping($this->getId());

			if($mail->getScheduledFor() && $mail->getScheduledFor()->format("Y-m-d") == $today_date && $mail->getScheduledFor()->format("H:i:s") <= $today_time && !$mail->getCourseIsStarted()) {
				$ilLog->write("gevReminderWebinarJob::run: Sending mail to $crs_id");

				try {
					$mail->send();
				}
				catch (Exception $e) {
					$ilLog->write("gevReminderWebinarJob::run: error when sending mail reminder_webinare. ".$e->getMessage());
				}
				// i'm alive!
				ilCronManager::ping($this->getId());
			} else {
				$ilLog->write("gevReminderWebinarJob::run: not send to $crs_id");
			}
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}