<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * TODO: import/export reminder data with the exercise/assignment.
 * TODO: Mail templates setup + Mail templates in Assignment.
 * TODO: Send the notifications.
 * TODO: Delete reminders from exc_ass_reminders when the assignment is deleted.
 *
 * Exercise Assignment Reminders
 *
 * @author Jesús López <lopez@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExAssignmentReminder
{
	const SUBMIT_REMINDER = "submit";
	const GRADE_REMINDER = "grade";
	const FEEDBACK_REMINDER = "peer";

	protected $db;
	protected $tree;
	protected $now;

	protected $rmd_status;
	protected $rmd_start;
	protected $rmd_end;
	protected $rmd_frequency;
	protected $rmd_last_send;
	protected $rmd_tpl_id;

	protected $ass_id;
	protected $exc_id;
	protected $rmd_type;

	//todo remove the params as soon as possible.
	function __construct($a_exc_id = "", $a_ass_id = "", $a_type = "")
	{
		global $DIC;
		$this->db = $DIC->database();
		$this->tree = $DIC->repositoryTree();

		if($a_ass_id) {
			$this->ass_id = $a_ass_id;
		}
		if($a_exc_id) {
			$this->exc_id = $a_exc_id;
		}
		if($a_type) {
			$this->rmd_type = $a_type;
		}
		if($a_exc_id and $a_ass_id and $a_type) {
			$this->read();
		}
	}

	public function getReminderType()
	{
		return $this->rmd_type;
	}

	/**
	 * Set reminder for users without submission.
	 * @param $a_status
	 */
	function setReminderStatus($a_status)
	{
		$this->rmd_status = $a_status;
	}

	/**
	 * Get the reminder status
	 * @return mixed
	 */
	function getReminderStatus()
	{
		return $this->rmd_status;
	}

	/**
	 * Set num days before the deadline to start sending notifications.
	 * @param $a_num_days
	 */
	function setReminderStart($a_num_days)
	{
		$this->rmd_start = $a_num_days;
	}

	/**
	 * Get num days before the deadline to start sending notifications.
	 * @return mixed
	 */
	function getReminderStart()
	{
		return $this->rmd_start;
	}

	/**
	 * Set the ending of the reminder
	 * @param $a_date
	 */
	function setReminderEnd($a_date)
	{
		$this->rmd_end = $a_date;
	}

	/**
	 * get the ending of the reminder
	 * @return mixed
	 */
	function getReminderEnd()
	{
		return $this->rmd_end;
	}

	/**
	 * Set frequency in days
	 * @param $a_num_days
	 */
	function setReminderFrequency($a_num_days)
	{
		$this->rmd_frequency = $a_num_days;
	}

	/**
	 * get submit reminder frequency in days.
	 * @return mixed
	 */
	function getReminderFrequency()
	{
		return $this->rmd_frequency;
	}

	function setReminderLastSend($a_timestamp)
	{
		$this->rmd_last_send = $a_timestamp;
	}

	function getReminderLastSend()
	{
		return $this->rmd_last_send;
	}

	function setReminderMailTemplate($a_tpl_id)
	{
		$this->rmd_tpl_id = $a_tpl_id;
	}

	function getReminderMailTemplate()
	{
		return $this->rmd_tpl_id;
	}

	public function save()
	{
		$this->db->insert("exc_ass_reminders", array(
			"type" => array("text", $this->rmd_type),
			"ass_id" => array("integer", $this->ass_id),
			"exc_id" => array("integer", $this->exc_id),
			"status" => array("integer", $this->getReminderStatus()),
			"start" => array("integer", $this->getReminderStart()),
			"end" => array("integer", $this->getReminderEnd()),
			"freq" => array("integer", $this->getReminderFrequency()),
			"last_send" => array("integer", $this->getReminderLastSend()),
			"template_id" => array("integer", $this->getReminderMailTemplate())
		));
	}

	public function update()
	{
		$this->db->update("exc_ass_reminders", array(
			"status" => array("integer", $this->getReminderStatus()),
			"start" => array("integer", $this->getReminderStart()),
			"end" => array("integer", $this->getReminderEnd()),
			"freq" => array("integer", $this->getReminderFrequency()),
			"last_send" => array("integer", $this->getReminderLastSend()),
			"template_id" => array("integer", $this->getReminderMailTemplate())
		),
		array(
			"type" => array("text", $this->rmd_type),
			"exc_id" => array("integer", $this->exc_id),
			"ass_id" => array("integer", $this->ass_id)
		));
	}


	public function read()
	{
		$set = $this->db->query("SELECT status, start, freq, end, last_send, template_id".
			" FROM exc_ass_reminders".
			" WHERE type ='".$this->rmd_type."'".
			" AND ass_id = ".$this->ass_id.
			" AND exc_id = ".$this->exc_id);

		$rec = $this->db->fetchAssoc($set);
		if(is_array($rec))
		{
			$this->initFromDB($rec);
		}
	}

	/**
	 * Import DB record
	 * @param array $a_set
	 */
	protected function initFromDB(array $a_set)
	{
		$this->setReminderStatus($a_set["status"]);
		$this->setReminderStart($a_set["start"]);
		$this->setReminderEnd($a_set["end"]);
		$this->setReminderFrequency($a_set["freq"]);
		$this->setReminderLastSend($a_set["last_send"]);
		$this->setReminderMailTemplate($a_set["template_id"]);
	}


	// CRON STUFF
	/**
	 * Get reminders available by date/frequence.
	 * @return mixed
	 */
	function getReminders()
	{
		$now = time();
		//remove time from the timestamp (86400 = 24h)
		$now = floor($now/86400)*86400;

		$query = "SELECT ass_id, exc_id, status, start, freq, end, type, last_send, template_id".
			" FROM exc_ass_reminders".
			" WHERE status = 1".
			" AND start <= ".$now.
			" AND end > ".$now;

		$result = $this->db->query($query);

		$array_data = array();
		while($rec = $this->db->fetchAssoc($result))
		{
			$rem = array(
				"ass_id" => $rec["ass_id"],
				"exc_id" => $rec["exc_id"],
				"start" => $rec["start"],
				"end" => $rec["end"],
				"freq" => $rec["freq"],
				"type" => $rec["type"],
				"last_send" => $rec["last_send"],
				"template_id" => $rec["template_id"]
			);

			//frequency
			$next_send = strtotime("-".$rec["freq"]." day", $now);
			if(!$rec["last_send"] || $next_send >= floor($rec["last_send"]/86400)*86400)
			{
				array_push($array_data,$rem);
			}
		}

		return $array_data;
	}


	/**
	 * Filter the reminders by object(crs,grp) by active status and if have members.
	 * @param $a_reminders
	 * @return array
	 */
	function parseReminders($a_reminders)
	{
		$reminders = $a_reminders;
		$users_to_remind = array();

		foreach($reminders as $rem)
		{
			$ass_id = $rem["ass_id"];
			$ass_obj = new ilExAssignment($ass_id);

			$exc_id = $rem["exc_id"];

			$refs = ilObject::_getAllReferences($exc_id);
			$exc_ref = end($refs);

			if($course_ref_id = $this->tree->checkForParentType($exc_ref, 'crs')) {
				$obj = new ilObjCourse($course_ref_id);
				$participants_class = "ilCourseParticipants";
				$parent_ref_id = $course_ref_id;
			} else if ($group_ref_id = $parent_ref_id = $this->tree->checkForParentType($exc_ref, 'grp')) {
				$obj = new ilObjGroup($group_ref_id);
				$participants_class = "ilGroupParticipants";
				$parent_ref_id = $group_ref_id;
			} else {
				continue;
			}

			 //TODO should we use getOfflineStatus instead of isActivated?
			if($obj->isActivated())
			{
				$parent_obj_id = $obj->getId();
				$participants_ids = $participants_class::getInstance($parent_ref_id)->getMembers();

				foreach($participants_ids as $member_id)
				{
					$submission = new ilExSubmission($ass_obj, $member_id);

					if(!$submission->getLastSubmission())
					{
						$member_data = array(
							"parent_type" => "crs",
							"parent_id" => $parent_obj_id,
							"exc_id" => $exc_id,
							"ass_id" => $ass_id,
							"member_id" => $member_id,
							"reminder_type" => $rem["type"],
							"template_id" => $rem["template_id"]
						);
						array_push($users_to_remind, $member_data);
					}
				}
			}
		}
		return $users_to_remind;
	}

	/**
	 * send reminders
	 */
	public function sendReminders()
	{
		$reminders = $this->getReminders();
		$reminders = $this->parseReminders($reminders);


		//todo send notification
		return true;
	}
}