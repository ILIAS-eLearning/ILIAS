<?php

class ilExAssignmentMemberStatus
{
	
	
//// Functions from ilExerciseMembers -> migrate!
////
	
	/**
	 * Lookup a field value of ass/member table
	 */
	private function lookupAssMemberField($a_ass_id, $a_user_id, $a_field)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT ".$a_field." FROM exc_mem_ass_status ".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		
		return $rec[$a_field];
	}
	
	/**
	 * Update a field value of ass/member table
	 */
	private function updateAssMemberField($a_ass_id, $a_user_id, $a_field, $a_value, $a_type)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE exc_mem_ass_status SET ".
			" ".$a_field." = ".$ilDB->quote($a_value, $a_type).
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer")
			);
	}
	

/*	function setStatus($a_status)
	{
		if(is_array($a_status))
		{
			$this->status = $a_status;
			return true;
		}
	}
	function getStatus()
	{
		return $this->status ? $this->status : array();
	}*/
	
	/**
	 * Lookup comment for the user
	 */
	function lookupCommentForUser($a_ass_id, $a_user_id)
	{
		return self::lookupAssMemberField($a_ass_id, $a_user_id, "u_comment");
	}

	/**
	 * Update comment
	 */
	function updateCommentForUser($a_ass_id, $a_user_id, $a_value)
	{
		self::updateAssMemberField($a_ass_id, $a_user_id,
			"u_comment", $a_value, "text");
	}

	/**
	 * Lookup user mark
	 */
	function lookupMarkOfUser($a_ass_id, $a_user_id)
	{
		return self::lookupAssMemberField($a_ass_id, $a_user_id, "mark");
	}

	/**
	 * Update mark
	 */
	function updateMarkOfUser($a_ass_id, $a_user_id, $a_value)
	{
		self::updateAssMemberField($a_ass_id, $a_user_id,
			"mark", $a_value, "text");
	}

	/**
	 * was: getStatusByMember
	 */
	function lookupStatusOfUser($a_ass_id, $a_user_id)
	{
		$stat = self::lookupAssMemberField($a_ass_id, $a_user_id, "status");
		if ($stat == "")
		{
			$stat = "notgraded";
		}
		return $stat;
	}

	/**
	 * was: setStatusForMember($a_member_id,$a_status)
	 */
	function updateStatusOfUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET status = %s, status_time= %s ".
			" WHERE ass_id = %s AND usr_id = %s AND status <> %s ",
			array("text", "timestamp", "integer", "integer", "text"),
			array($a_status, ilUtil::now(), $a_ass_id, $a_user_id, $a_status));
		
		$ass = new ilExAssignment($a_ass_id);
		$exc = new ilObjExercise($ass->getExerciseId(), false);
		$exc->updateUserStatus($a_user_id);
	}

	/**
	 * was: updateStatusTimeForMember($a_user_id)
	 */
	function updateStatusTimeOfUser($a_ass_id, $a_user_id)
	{
		// #13741 - is only used for mark
		self::updateAssMemberField($a_ass_id, $a_user_id,
			"status_time", ilUtil::now(), "timestamp");
	}


	/*function setStatusSent($a_status)
	{
		if(is_array($a_status))
		{
			$this->status_sent = $a_status;
			return true;
		}
	}
	function getStatusSent()
	{
		return $this->status_sent ? $this->status_sent : array(0 => 0);
	}*/
	
	/**
	 * was: getStatusSentByMember($a_member_id)
	 */
	function lookupStatusSentOfUser($a_ass_id, $a_user_id)
	{
		return self::lookupAssMemberField($a_ass_id, $a_user_id, "sent");
	}
	
	/**
	 * was: setStatusSentForMember($a_member_id,$a_status)
	 */
	function updateStatusSentForUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		// #13741
		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET sent = %s, sent_time = %s ".
			" WHERE ass_id = %s AND usr_id = %s ",
			array("integer", "timestamp", "integer", "integer"),
			array((int) $a_status, ($a_status ? ilUtil::now() : null),
				$a_ass_id, $a_user_id));
	}

	/*function getStatusReturned()
	{
		return $this->status_returned ? $this->status_returned : array(0 => 0);
	}
	function setStatusReturned($a_status)
	{
		if(is_array($a_status))
		{
			$this->status_returned = $a_status;
			return true;
		}
		return false;
	}*/

	/**
	 * was: getStatusReturnedByMember($a_member_id)
	 */
	function lookupStatusReturnedOfUser($a_ass_id, $a_user_id)
	{
		return self::lookupAssMemberField($a_ass_id, $a_user_id, "returned");
	}

	/**
	 * was: setStatusReturnedForMember($a_member_id,$a_status)
	 */
	function updateStatusReturnedForUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;
		
		// first upload => notification on submission?
		if($a_status &&
			!self::lookupStatusReturnedOfUser($a_ass_id, $a_user_id))
		{
			$set = $ilDB->query("SELECT fb_cron, fb_date, fb_file".
				" FROM exc_assignment".
				" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
			$row = $ilDB->fetchAssoc($set);
			if($row["fb_cron"] &&
				$row["fb_file"] &&
				$row["fb_date"] == self::FEEDBACK_DATE_SUBMISSION)
			{
				self::sendFeedbackNotifications($a_ass_id, $a_user_id);
			}
		}

		// #13741
		$ilDB->manipulateF("UPDATE exc_mem_ass_status".
			" SET returned = %s".
			" WHERE ass_id = %s AND usr_id = %s",
			array("integer", "integer", "integer"),
			array((int) $a_status, $a_ass_id, $a_user_id));
	}

/*	// feedback functions
	function setStatusFeedback($a_status)
	{
		if(is_array($a_status))
		{
			$this->status_feedback = $a_status;
			return true;
		}
	}
	function getStatusFeedback()
	{
		return $this->status_feedback ? $this->status_feedback : array(0 => 0);
	}*/
	
	/**
	 * was: getStatusFeedbackByMember($a_member_id)
	 */
	function lookupStatusFeedbackOfUser($a_ass_id, $a_user_id)
	{
		return self::lookupAssMemberField($a_ass_id, $a_user_id, "feedback");
	}

	/**
	 * was: setStatusFeedbackForMember($a_member_id,$a_status)
	 */
	function updateStatusFeedbackForUser($a_ass_id, $a_user_id, $a_status)
	{
		global $ilDB;

		// #13741
		$ilDB->manipulateF("UPDATE exc_mem_ass_status ".
			"SET feedback = %s, feedback_time = %s ".
			" WHERE ass_id = %s AND usr_id = %s",
			array("integer", "timestamp", "integer", "integer"),
			array((int) $a_status, ($a_status ? ilUtil::now() : null),
				$a_ass_id, $a_user_id));
	}

	/**
	 * Get time when exercise has been sent per e-mail to user
	 */
	static function lookupSentTimeOfUser($a_ass_id, $a_user_id)
	{
		return ilUtil::getMySQLTimestamp(
			self::lookupAssMemberField($a_ass_id, $a_user_id, "sent_time"));
	}

	/**
	 * Get time when feedback mail has been sent.
	 */
	static function lookupFeedbackTimeOfUser($a_ass_id, $a_user_id)
	{
		return ilUtil::getMySQLTimestamp(
			self::lookupAssMemberField($a_ass_id, $a_user_id, "feedback_time"));
	}
	
	/**
	 * Get status time
	 */
	static function lookupStatusTimeOfUser($a_ass_id, $a_user_id)
	{
		return ilUtil::getMySQLTimestamp(
			self::lookupAssMemberField($a_ass_id, $a_user_id, "status_time"));
	}

	/*function getNotice()
	{
		return $this->notice ? $this->notice : array(0 => 0);
	}

	function setNotice($a_notice)
	{
		if(is_array($a_notice))
		{
			$this->notice = $a_notice;
			return true;
		}
		return false;
	}*/

	/**
	 * was: getNoticeByMember($a_member_id)
	 */
	function lookupNoticeOfUser($a_ass_id, $a_user_id)
	{
		return self::lookupAssMemberField($a_ass_id, $a_user_id, "notice");
	}


	
	/**
	 * was: setNoticeForMember($a_member_id,$a_notice)
	 */
	function updateNoticeForUser($a_ass_id, $a_user_id, $a_notice)
	{
		global $ilDB;
		
		// #12181 / #13741
		$ilDB->manipulate("UPDATE exc_mem_ass_status".
			" SET notice = ".$ilDB->quote($a_notice, "text").		
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
			" AND ".$ilDB->equalsNot("notice", $a_notice, "text", true));
	}

	/**
	 * Check whether exercise has been sent to any student per mail.
	 */
	static function lookupAnyExerciseSent($a_exc_id, $a_ass_id)
	{
  		global $ilDB;

  		$q = "SELECT count(*) AS cnt FROM exc_mem_ass_status".
			" WHERE NOT sent_time IS NULL".
			" AND ass_id = ".$ilDB->quote($a_ass_id, "integer")." ".
			" ";
		$set = $ilDB->query($q);
		$rec = $ilDB->fetchAssoc($set);

		if ($rec["cnt"] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}

