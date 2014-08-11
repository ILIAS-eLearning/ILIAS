<?php

require_once("Services/GEV/Mailing/classes/class.gevRegistrationMail.php");

class gevEVGActivationMail extends gevRegistrationMail {
	public function getTitle() {
		return "Accountaktivierung";
	}
	
	public function _getDescription() {
		return "Benutzer registriert sich über EVG-IV-Import.";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "EVG_Aktivierung";
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
		return   ($this->bill !== null)
				 ? $this->bill->getRecipientName()
				 : $ilUser->getFullname();
	}
	
	protected function getEmailForTemplate($a_recipient) {
		global $ilUser;
		return   ($this->bill !== null)
				 ? $this->bill->getRecipientEmail()
				 : $ilUser->getEmail();
	}
	
	public function sendBill(ilBill $a_bill) {
		$context_id = $a_bill->getContextId();
		
		if ($context_id != $this->crs_id) {
			throw new Exception( "gevCrsBillMail::sendBill: context id of bill '".$a_bill->getId()
								."'' does not match crs_id '".$this->crs_id."'");
		}
		
		$this->bill = $a_bill;
		
		$a_recipients = array( array( "email" => $a_bill->getRecipientEmail()
									, "name" => $a_bill->getRecipientName()
									)
							 );
		return $this->send($a_recipients);
	}

	public function getMail($a_recipient) {
		$mail = $this->getMessage($this->getTemplateId(), $a_recipient);
		$mail["to"] = $a_recipient["name"]." <".$a_recipient["email"].">";
		return $mail;	
	}
	
	public function getMessageFromTemplate($a_templ_id, $a_user_id, $a_email, $a_name) {
		global $ilUser;
		$user_id = ($this->bill !== null)
				 ? $this->bill->getUserId()
				 : $ilUser->getId();
				 
		return parent::getMessageFromTemplate($a_templ_id, $user_id, $a_email, $a_name);
	}
	
	public function getAttachmentsForMail() {
		if ($this->bill === null) {
			return;
		}
		require_once("Services/GEV/Utils/classes/class.gevBillStorage.php");
		
		return array( array( "name" => $this->bill->getBillNumber().".pdf"
						   , "path" => gevBillStorage::getInstance()->getPath($this->bill)
						   )
					);
	}
}

?>