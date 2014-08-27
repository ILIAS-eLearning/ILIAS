<?php

class gevDeferredMails {
	static $instance = null;
	
	protected function __construct() {
		global $ilDB;
		$this->db = &$ilDB;
	}
	
	static public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevDeferredMails();
		}
		return self::$instance;
	}
	
	public function deferredSendMail($a_crs_id, $a_mail_id, $a_recipients, $a_occasion) {
		//print_r($a_crs_id);
		//print_r($a_mail_id);
		//print_r($a_recipients);
		//print_r($a_occasion);
		//die();
		foreach ($a_recipients as $recipient) {
			$send = $this->deferredMailNeedsToBeSend($a_crs_id, $a_mail_id, $a_recipient);
			$this->removeOutdatedDeferredMails($a_crs_id, $a_mail_id, $recipient);
			
			if(!$send) {
				continue;
			}
			
			$this->db->manipulate("INSERT INTO gev_crs_deferred_mails (crs_id, mail_id, recipient, occasion) VALUES ".
								  "      ( ".$this->db->quote($a_crs_id, "integer").
								  "      , ".$this->db->quote($a_mail_id, "text").
								  "      , ".$this->db->quote($recipient, "text").
								  "      , ".$this->db->quote($a_occasion, "text").
								  "      ) ".
								  " ON DUPLICATE KEY UPDATE occasion = ".$this->db->quote($a_occasion, "text")
								 );
		}
	}
	
	// removes all mails matching the criteria defined via the parameters.
	// parameters should be arrays.
	public function removeDeferredMails($a_crs_ids, $a_mail_ids = null, $a_recipients = null) {
		$this->db->manipulate("DELETE FROM gev_crs_deferred_mails ".
							  " WHERE ".$this->db->in("crs_id", $a_crs_ids, false, "integer").
							  (($a_mail_ids === null)?""
							   : " AND ".$this->db->in("mail_id", $a_mail_ids, false, "text")).
							  (($_recipients === null)?""
							   : " AND ".$this->db->in("recipient", $a_recipients, false, "text"))
							  );
	}
	
	// This checks weather previously deferred mails are outdated, e.g. invitation is
	// outdated when user is removed from course.
	protected function removeOutdatedDeferredMails($a_crs_id, $a_mail_id, $a_recipient) {
		switch ($a_mail_id) {
			case "participant_sucessfull":
			case "participant_absent_excused":
			case "participant_absent_not_excused":
				$this->removeDeferredMails( array($crs_id)
										  , array( "participant_sucessfull"
										  		 , "participant_absent_excused"
										  		 , "participant_absent_not_excused"
										  		 )
										  , array($a_recipient)
										  );
				break;
			case "trainer_added":
			case "trainer_removed":
				$this->removeDeferredMails( array($crs_id)
										  , array( "trainer_added"
										  		 , "trainer_removed"
										  		 )
										  , array($a_recipient)
										  );
			case "admin_cancel_booked_to_cancelled_without_costs":
			case "admin_cancel_waiting_to_cancelled_without_costs":
				$this->removeDeferredMails( array( $crs_id)
										  , array( "admin_booking_to_waiting"
										  		 , "admin_booking_to_booked"
										  		 , "invitation"
												 )
										  , array($a_recipient)
										  );
		}
	}
	
	// This checks weather a deferred mail needs to be send at alls, e.g. cancellation
	// needs not to be send if user was not invited.
	protected function deferredMailNeedsToBeSend($a_crs_id, $a_mail_id, $a_recipient) {
		switch ($a_mail_id) {
			case "trainer_added":
			case "trainer_removed":
				return count($this->getDeferredMails( array( $crs_id)
													, array( "trainer_added"
														   , "trainer_removed"
														   )
													, array($a_recipient)
													)) == 0;
			case "admin_cancel_booked_to_cancelled_without_costs":
			case "admin_cancel_waiting_to_cancelled_without_costs":
				return count($this->getDeferredMails( array( $crs_id)
													, array( "admin_booking_to_waiting"
														   , "admin_booking_to_booked"
														   )
													, array($a_recipient)
													)) == 0;
			default:
				return true;
		}
	}
	
	// get all deferred mails ordered by crs_id. Return an array containing arrays
	// with crs_id, mail_id, recipient and occasion. Deferred mails need to be removed
	// via removeDeferredMails after sending.
	public function getDeferredMails($a_crs_ids = null, $a_mail_ids = null, $a_recipients = null) {
		$res = $this->db->query("SELECT * FROM gev_crs_deferred_mails WHERE 1 = 1 ".
								(($a_crs_ids === null)?""
								: " AND ".$this->db->in("crs_id", $a_crs_ids, false, "integer")).
								(($a_mail_ids === null)?""
							 	: " AND ".$this->db->in("mail_id", $a_mail_ids, false, "text")).
								(($_recipients === null)?""
								: " AND ".$this->db->in("recipient", $a_recipients, false, "text"))
								." ORDER BY crs_id");
		$ret = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$ret[] = $rec;
		}
		return $ret;
	}
	
	public function sendDeferredMails($a_crs_ids = null, $a_mail_ids = null, $a_recipients = null) {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		global $ilLog;
		
		$mails = $this->getDeferredMails($a_crs_ids, $a_mail_ids, $a_recipients);
		$cur_crs_id = null;
		$automails = null;
		foreach ($mails as $mail) {
			if ($cur_crs_id != $mail["crs_id"]) {
				$cur_crs_id = $mail["crs_id"];
				$automails = new gevCrsAutoMails($cur_crs_id);
			}

			try {
				$automails->send($mail["mail_id"], array($mail["recipient"]), $mail["occasion"]);
				$ilLog->write("gevDeferredMails::sendDeferredMails: send mail crs_id = ".$mail["crs_id"].", mail_id = ".$mail["mail_id"].", recipient = ".$mail["recipient"]);
			}
			catch (Exception $e) {
				$ilLog->write("gevDeferredMails::sendDeferredMails: ERROR ".$e);
			}

			$this->removeDeferredMails(array($mail["crs_id"]), array($mail["mail_id"]), array($mail["recipient"]));
		}
	}
}

?>