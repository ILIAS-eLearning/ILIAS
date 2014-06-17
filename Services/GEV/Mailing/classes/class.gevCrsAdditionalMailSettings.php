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

	public function __construct($a_crs_id) {
		global $ilDB, $ilCtrl;
		$this->db = &$ilDB;
		$this->ctrl = &$ilCtrl;

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
		return $this->settings["invitation_mailing_date"];
	}
	
	public function setInvitationMailingDate($a_date) {
		if (gettype($a_date) != "integer") {
			throw new Exception("gevCrsAdditionalMailSettings::setInvitationMailingDate expected integer as first argument, ".gettype($a_date)." given.");
		}
		
		$this->settings["invitation_mailing_date"] = $a_date;
	}

	protected function read() {
		$this->settings = array();

		$result = $this->db->query("SELECT send_list_to_accom, send_list_to_venue, inv_mailing_date
									FROM gev_crs_addset
									WHERE crs_id = ".$this->db->quote($this->crs_id));

		if ($record = $this->db->fetchAssoc($result)) {
			$this->settings = array( "send_list_to_accom" => $record["send_list_to_accom"] != 0
								   , "send_list_to_venue" => $record["send_list_to_venue"] != 0
								   );
		}
		else {
			$this->settings = array( "send_list_to_accom" => true
								   , "send_list_to_venue" => true
								   );
		}
	}

	public function save() {
		$query = "INSERT INTO gev_crs_addset (crs_id, send_list_to_accom, send_list_to_venue, inv_mailing_date)
				  VALUES ".
				"(".$this->db->quote($this->crs_id, "integer").", "
				   .$this->db->quote($this->settings["send_list_to_accom"]?1:0, "integer").", "
				   .$this->db->quote($this->settings["send_list_to_venue"]?1:0, "integer").", "
				   .$this->db->quote($this->settings["invitation_mailing_date"], "integer").
				") 
				ON DUPLICATE KEY UPDATE
				 	send_list_to_accom = ".$this->db->quote($this->settings["send_list_to_accom"]?1:0, "integer").",
					send_list_to_venue = ".$this->db->quote($this->settings["send_list_to_venue"]?1:0, "integer").",
					inv_mailing_date = ".$this->db->quote($this->settings["invitation_mailing_date"], "integer");

		$this->db->manipulate($query);
	}

	public function copyTo($a_crs_id) {
		$other = new gevCrsAdditionalMailSettings($a_crs_id);

		$other->setSendListToAccomodation($this->getSendListToAccomodation());
		$other->setSendListToVenue($this->getSendListToVenue());
		$other->setInvitationMailingDate($this->getInvitationMailingDate());

		$other->save();
	}
}


?>