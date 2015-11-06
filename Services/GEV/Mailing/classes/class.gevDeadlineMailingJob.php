<?php

require_once("Services/Cron/classes/class.ilCronManager.php");
require_once("Services/Cron/classes/class.ilCronJob.php");
require_once("Services/Cron/classes/class.ilCronJobResult.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");


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
		global $ilDB, $ilLog;

		$this->db = $ilDB;
		$this->log = $ilLog;

		require_once("Services/GEV/Mailing/classes/CrsMails/class.gevParticipationStatusNotSet.php");
		
		$this->deadline_jobs = array(
		  "min_participants_not_reached"
		, "reminder_participants"
		, "reminder_trainer"
		, "list_for_accomodation"
		, "updated_list_for_accomodation"
		, "participation_status_not_set"
		, "invitation"
		, "materiallist_for_storage"
		, "min_participants_not_reached_six_weeks"
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
		
		$this->initCronMailData();

		$end_date_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_END_DATE);
		$start_date_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_START_DATE);
		$is_template_field_id = gevSettings::getInstance()->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE);
		$num_jobs = count($this->deadline_jobs);
		
		$cron_result = new ilCronJobResult();

		// get all courses where the end date plus the maximum after course end date + 2 days 
		// (1 day for not knowing when the cron did run, 1 day in case there is some problem)
		// did not expire.
		$safety_margin = 2; // days
		
		$query = "SELECT DISTINCT cs.obj_id ".
 				 "  FROM crs_settings cs ".
 				 "  LEFT JOIN object_reference oref".
				 "    ON cs.obj_id = oref.obj_id".
				 "  JOIN adv_md_values_text is_template ".
				 "    ON cs.obj_id = is_template.obj_id ".
				 "   AND is_template.field_id = ".$this->db->quote($is_template_field_id, "integer").
				 "  LEFT JOIN adv_md_values_date end_date ".
				 "    ON cs.obj_id = end_date.obj_id ".
				 "   AND end_date.field_id = ".$this->db->quote($end_date_field_id, "integer").
				 "  JOIN adv_md_values_date start_date ".
				 "    ON cs.obj_id = start_date.obj_id ".
				 "   AND start_date.field_id = ".$this->db->quote($start_date_field_id, "integer").
				 " WHERE (  ( ADDDATE(end_date.value, ".$this->max_after_course_end." + ".$safety_margin.")".
				 "            >= ".$this->db->quote(date("Y-m-d"), "date").")".
				 "       OR ( end_date.value IS NULL AND ".
				 "            ADDDATE(start_date.value, ".$this->max_after_course_end." + ".$safety_margin.")".
				 "            >= ".$this->db->quote(date("Y-m-d"), "date").")".
				 "       )".
				 "   AND is_template.value <> '".gevSettings::YES."'".
				 "   AND oref.deleted IS NULL".
				 "";
		
		$res = $this->db->query($query);
		$now = new ilDateTime(time(), IL_CAL_UNIX);
		
		while ($rec = $this->db->fetchAssoc($res)) {
			$crs_id = $rec["obj_id"];
			$crs_utils = gevCourseUtils::getInstance($crs_id);
			$this->log->write("ilDeadlineMailingJob::run: Checking course ".$crs_id.".");
			$auto_mails = new gevCrsAutoMails($crs_id);
			
			// determine which mails where already send for the course and which need to be
			// send
			$res2 = $this->db->query("SELECT title FROM gev_crs_dl_mail_cron WHERE crs_id = ".$this->db->quote($crs_id, "integer"));
			$mails_send = array();
			while($rec2 = $this->db->fetchAssoc($res2)) {
				$mails_send[] = $rec2["title"];
			}
			$mails_to_send = array_diff($this->deadline_jobs, $mails_send);
			
			$this->log->write("ilDeadlineMailingJob::run: mails to send = ".implode(", ", $mails_to_send));
			
			// send the mails that need to be send and store the fact, the mails where send, in the
			// deadline mailing table.
			foreach ($mails_to_send as $key) {
				$mail = $auto_mails->getAutoMail($key);
				$scheduled_time = $mail->getScheduledFor();
				
				if ($scheduled_time === null) {
					$this->log->write("ilDeadlineMailingJob::run: Expected mail ".$key." to have a scheduled time, but got none.");
					continue;
				}
				
				$this->log->write("lDeadlineMailingJob::run: Mail ".$key." scheduled for ".$scheduled_time->get(IL_CAL_DATETIME).".");
				
				if ( !ilDateTime::_before($scheduled_time, $now) ) {
					continue;
				}
				
				if ($mail->shouldBeSend()) {
					$this->log->write("ilDeadlineMailingJob::run: Send mail ".$key.".");
					try {
						$mail->send();
					}
					catch (Exception $e) {
						$this->log->write("ilDeadlineMailingJob::run: error when sending mail ".$key.".");
						$this->log->write("ilDeadlineMailingJob::run: error when sending mail error message".$e->getMessage().".");
					}
				}
				else {
					$this->log->write("ilDeadlineMailingJob:run: No need to send Mail.");
				}
				
				$this->setIsSend($crs_id, $key);
			
				ilCronManager::ping($this->getId());
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

	private function setIsSend($a_crs_id, $a_key) {
		$this->db->manipulate("INSERT INTO gev_crs_dl_mail_cron (crs_id, title, send_at) VALUES ".
								  "    ( ".$this->db->quote($a_crs_id, "integer").
								  "    , ".$this->db->quote($a_key, "text").
								  "    , NOW()".
								  "    )"
								 );
	}
}

?>