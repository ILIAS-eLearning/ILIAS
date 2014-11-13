<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");


class gevDeadlineMailingJob extends ilCronJob {
	public function getId() {
		return "gev_deadline_mailing";
	}
	
	public function getTitle() {
		return "Mailing für Trainings";
	}

	public function hasAutoActivation() {
		return true;
	}
	
	public function hasFlexibleSchedule() {
		return false;
	}
	
	public function getDefaultScheduleType() {
		return ilCronJob::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue() {
		return 1;
	}
	
	public function initCronMailData() {
		require_once("Services/GEV/Mailing/classes/CrsMails/class.gevParticipationStatusNotSet.php");
		
		$this->deadline_jobs = array(
		  "min_participants_not_reached"
		, "reminder_participants"
		, "reminder_trainer"
		, "list_for_accomodation"
		, "updated_list_for_accomodation"
		, "participation_status_not_set"
		, "invitation"
		);
		
		$this->max_after_course_end = gevParticipationStatusNotSet::DAYS_AFTER_COURSE_END;
	}
	
	static public function isMailSend($a_crs_id, $a_mail_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT COUNT(*) cnt FROM gev_crs_dl_mail_cron ".
						    " WHERE crs_id = ".$ilDB->quote($a_crs_id, "integer").
						    "   AND title = ".$ilDB->quote($a_mail_id, "text").
						    "   AND NOT send_at IS NULL");
		$rec = $ilDB->fetchAssoc($res);
		return $rec["cnt"] > 0;
	}
	
	public function run() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		global $ilLog, $ilDB;
		
		$this->initCronMailData();
		$end_date_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
		$num_jobs = count($this->deadline_jobs);
		
		$cron_result = new ilCronJobResult();

		// get all courses where the end date plus the maximum after course end date + 2 days 
		// (1 day for not knowing when the cron did run, 1 day in case there is some problem)
		// did not expire.
		$safety_margin = 2; // days
		
		$query = "SELECT DISTINCT cs.obj_id ".
				 "  FROM crs_settings cs ".
				 " LEFT JOIN object_reference oref".
				 "   ON cs.obj_id = oref.obj_id".
				 /*"  JOIN adv_md_values_date start_date ".
				 "    ON cs.obj_id = start_date.obj_id ".
				 "   AND start_date.field_id = ".$ilDB->quote($start_date_field_id, "integer").*/
				 "  JOIN adv_md_values_date end_date ".
				 "    ON cs.obj_id = end_date.obj_id ".
				 "   AND end_date.field_id = ".$ilDB->quote($end_date_field_id, "integer").
				 " WHERE ADDDATE(end_date.value, -1 * ".$this->max_after_course_end." + ".$safety_margin.")".
				 "       >= ".$ilDB->quote(date("Y-m-d"), "date").
				 "   AND oref.deleted IS NULL".
				 "";
		
		$res = $ilDB->query($query);
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		
		while ($rec = $ilDB->fetchAssoc($res)) {
			$crs_id = $rec["obj_id"];
			$ilLog->write("ilDeadlineMailingJob::run: Checking course ".$crs_id.".");
			$auto_mails = new gevCrsAutoMails($crs_id);
			
			// determine which mails where already send for the course and which need to be
			// send
			$res2 = $ilDB->query("SELECT title FROM gev_crs_dl_mail_cron WHERE crs_id = ".$ilDB->quote($crs_id, "integer"));
			$mails_send = array();
			while($rec2 = $ilDB->fetchAssoc($res2)) {
				$mails_send[] = $rec2["title"];
			}
			$mails_to_send = array_diff($this->deadline_jobs, $mails_send);
			
			$ilLog->write("ilDeadlineMailingJob::run: mails to send = ".implode(", ", $mails_to_send));
			
			// send the mails that need to be send and store the fact, the mails where send, in the
			// deadline mailing table.
			foreach ($mails_to_send as $key) {
				$mail = $auto_mails->getAutoMail($key);
				$scheduled_time = $mail->getScheduledFor();
				
				if ($scheduled_time === null) {
					$ilLog->write("ilDeadlineMailingJob::run: Expected mail ".$key." to have a scheduled time, but got none.");
					continue;
				}
				
				$ilLog->write("lDeadlineMailingJob::run: Mail ".$key." scheduled for ".$scheduled_time->get(IL_CAL_DATETIME).".");
				
				if ( !ilDateTime::_before($scheduled_time, $now) ) {
					continue;
				}
				
				if ($mail->shouldBeSend()) {
					$ilLog->write("ilDeadlineMailingJob::run: Send mail ".$key.".");
					try {
						$mail->send();
					}
					catch (Exception $e) {
						$ilLog->write("ilDeadlineMailingJob::run: error when sending mail ".$key.".");
					}
				}
				else {
					$ilLog->write("ilDeadlineMailingJob:run: No need to send Mail.");
				}
				
				$ilDB->manipulate("INSERT INTO gev_crs_dl_mail_cron (crs_id, title, send_at) VALUES ".
								  "    ( ".$ilDB->quote($crs_id, "integer").
								  "    , ".$ilDB->quote($key, "text").
								  "    , NOW()".
								  "    )"
								 );
			}
			
			// test to avoid php idiosyncracies
			if (count($this->deadline_jobs) != $num_jobs) {
				throw new Exception("ilDeadlineMailingJob::run: array containing all jobs was modified unintentionally.");
			}
			
			// i'm alive!
			ilCronManager::ping($this->getId());
		}

		$cron_result->setStatus(ilCronJobResult::STATUS_OK);
		return $cron_result;
	}
}

?>