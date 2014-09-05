<?php

require_once("Services/GEV/Mailing/classes/class.gevRegistrationMail.php");

class gevAgentActivationMail extends gevRegistrationMail {
	public function getTitle() {
		return "Accountaktivierung";
	}
	
	public function _getDescription() {
		return "Benutzer registriert sich über Makler-IV-Import.";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "Makler_Aktivierung";
	}
	
	public function getRecipientUserIDs() {
		return array();
	}
	
	public function getRecipientAddresses() {
		$reg_data = $this->getRegistrationData();
		return array(array( "name" => $reg_data["firstname"]." ".$reg_data["lastname"]
						  , "email" => $reg_data["email"]
					));
	}
	
	public function getCC($a_recipient) {
		return array();
	}
	
	public function getUsersOnly() {
		return false;
	}
	
	protected function getFullnameForTemplate($a_recipient) {
		global $ilUser;
		return $ilUser->getFullname();
	}
	
	protected function getEmailForTemplate($a_recipient) {
		global $ilUser;
		return $ilUser->getEmail();
	}

	public function getMail($a_recipient) {
		$mail = $this->getMessage($this->getTemplateId(), $a_recipient);
		$mail["to"] = $a_recipient["name"]." <".$a_recipient["email"].">";
		return $mail;	
	}
	
	public function getMessageFromTemplate($a_templ_id, $a_user_id, $a_email, $a_name) {
		global $ilUser;
		$user_id = $ilUser->getId();
				 
		return parent::getMessageFromTemplate($a_templ_id, $user_id, $a_email, $a_name);
	}
	
	public function getAttachmentsForMail() {
		return;
	}
	
	protected function getRegistrationData() {
		if ($this->reg_data === null) {
			$sql = "SELECT login as username, firstname, lastname, gender, email ".
				   "  FROM usr_data ".
				   " WHERE reg_hash = ".$this->db->quote($this->token, "text")
				   ;

			$res = $this->db->query($sql);
			if ($rec = $this->db->fetchAssoc($res)) {
				$this->reg_data = $rec;
			}
			else {
				throw new Exception("gevRegistrationMail::getRegistrationData: could not read users registration data.");
			}
		}
		return $this->reg_data;
	}
}

?>