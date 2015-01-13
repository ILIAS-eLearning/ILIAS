<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevCrsInvitationMailSettings
*$
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevCrsAdditionalMailSettings {
	protected $crs_id;
	protected $settings;
	protected $db;
	
	const INVITATION_MAIL_DEFAULT_DATE = 0;

	public function __construct($a_crs_id) {
		global $ilDB, $ilCtrl;
		$this->db = &$ilDB;

		$this->crs_id = $a_crs_id;

		$this->read();
	}

	public function setSendListToAccomodation($a_send) {
		if (gettype($a_send) != "boolean") {
			throw new Exception("gevCrsAdditionalMailSettings::setSendListToAccomodation expected boolean as first argument, ".gettype($a_send)." given.");
		}
		
		$this->settings["send_list_to_accom"] = $a_send;
	}

	public function getSendListToAccomodation() {
		return $this->settings["send_list_to_accom"];
	}

	public function setSendListToVenue($a_send) {
		if (gettype($a_send) != "boolean") {
			throw new Exception("gevCrsAdditionalMailSettings::setSendListToVenue expected boolean as first argument, ".gettype($a_send)." given.");
		}
		
		$this->settings["send_list_to_venue"] = $a_send;
	}

	public function getSendListToVenue() {
		return $this->settings["send_list_to_venue"];
	}

	public function getInvitationMailingDate() {
		return $this->settings["inv_mailing_date"];
	}
	
	public function setInvitationMailingDate($a_date) {
		global $ilLog;
		if (gettype($a_date) != "integer") {
			throw new Exception("gevCrsAdditionalMailSettings::setInvitationMailingDate expected integer as first argument, ".gettype($a_date)." given.");
		}
		$ilLog->write("gevCrsAdditionalMailSettings::setInvitationMailingDate: changed of crs ".$this->crs_id." to ".$a_date);
		$this->settings["inv_mailing_date"] = $a_date;
	}
	
	public function setSuppressMails($a_suppress) {
		if ($this->settings["suppress_mails"] && !$a_suppress) {
			throw new Exception("gevCrsAdditionalMailSettings::setSupressMails: You are not allowed to turn this off again.");
		}
		
		$this->settings["suppress_mails"] = $a_suppress;
	}

	public function getSuppressMails() {
		return $this->settings["suppress_mails"];
	}

	protected function read() {
		$this->settings = array();

		$result = $this->db->query("SELECT send_list_to_accom, send_list_to_venue, inv_mailing_date, suppress_mails
									FROM gev_crs_addset
									WHERE crs_id = ".$this->db->quote($this->crs_id));

		if ($record = $this->db->fetchAssoc($result)) {
			$this->settings = array( "send_list_to_accom" => $record["send_list_to_accom"] != 0
								   , "send_list_to_venue" => $record["send_list_to_venue"] != 0
								   , "inv_mailing_date" => $record["inv_mailing_date"] ? intval($record["inv_mailing_date"])
								   													   : self::INVITATION_MAIL_DEFAULT_DATE
								   , "suppress_mails" => $record["suppress_mails"] != 0
								   );
		}
		else {
			$this->settings = array( "send_list_to_accom" => true
								   , "send_list_to_venue" => true
								   , "inv_mailing_date" => self::INVITATION_MAIL_DEFAULT_DATE
								   , "suppress_mails" => false
								   );
		}
	}

	public function save() {
		$query = "INSERT INTO gev_crs_addset (crs_id, send_list_to_accom, send_list_to_venue, inv_mailing_date, suppress_mails)
				  VALUES ".
				"(".$this->db->quote($this->crs_id, "integer").", "
				   .$this->db->quote($this->settings["send_list_to_accom"]?1:0, "integer").", "
				   .$this->db->quote($this->settings["send_list_to_venue"]?1:0, "integer").", "
				   .$this->db->quote($this->settings["inv_mailing_date"], "integer").", "
				   .$this->db->quote($this->settings["suppress_mails"]?1:0, "integer").
				") 
				ON DUPLICATE KEY UPDATE
				 	send_list_to_accom = ".$this->db->quote($this->settings["send_list_to_accom"]?1:0, "integer").",
					send_list_to_venue = ".$this->db->quote($this->settings["send_list_to_venue"]?1:0, "integer").",
					inv_mailing_date = ".$this->db->quote($this->settings["inv_mailing_date"], "integer").",
					suppress_mails = ".$this->db->quote($this->settings["suppress_mails"]?1:0, "integer");

		$this->db->manipulate($query);
	}

	public function copyTo($a_crs_id) {
		$other = new gevCrsAdditionalMailSettings($a_crs_id);

		$inv_mail_date = $this->getInvitationMailingDate();

		$other->setSendListToAccomodation($this->getSendListToAccomodation());
		$other->setSendListToVenue($this->getSendListToVenue());
		$other->setInvitationMailingDate($inv_mail_date ? $inv_mail_date : self::INVITATION_MAIL_DEFAULT_DATE);
		$other->setSuppressMails($this->getSuppressMails());

		$other->save();
	}
}


?>