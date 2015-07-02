<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevCancellationMailForStorage extends gevCrsAutoMail {
	public function getTitle() {
		return "Absage Lager";
	}
	
	public function _getDescription() {
		return "Training wird vom Admin storniert";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function shouldBeSend() {
		if (!$this->getCourseUtils()->isPraesenztraining()) {
			return false;
		}
		
		if ($this->getCourseUtils()->isTemplate()) {
			return false;
		}
		
		if (!$this->getCourseUtils()->hasMaterialOnList()) {
			return false;
		}
		
		return parent::shouldBeSend();
	}
	
	public function getTemplateCategory() {
		return "C16";
	}
	
	public function getUsersOnly() {
		return false;
	}
	
	public function getRecipientAddresses() {
		return array(array( "name" => "Materiallager"
						  , "email" => "formularlager-generali-zd.versicherungen.de@generali.com"
						  )
					);
	}
	
	protected function getFullnameForTemplate($a_recipient) {
		return $a_recipient["name"];
	}
	
	protected function getEmailForTemplate($a_recipient) {
		return $a_recipient["email"];
	}
	
	public function getMessageFromTemplate($a_template_id, $a_recipient, $a_fullname, $a_email) {
		return parent::getMessageFromTemplate($a_template_id, null, $a_fullname, $a_email);
	}
	
	public function getMail($a_recipient) {
		if (!$this->getCourseUtils()->isPraesenztraining()) {
			return null;
		}
		
		if ($this->getCourseUtils()->isTemplate()) {
			return null;
		}
		
		if (!$this->getCourseUtils()->hasMaterialOnList()) {
			return null;
		}

		if ($this->checkUserID($a_recipient)) {
			$a_recipient = array( "name" => ilObjUser::_lookupFullname($a_recipient)
								, "email" => ilObjUser::_lookupEmail($a_recipient));
		}
		
		$message = $this->getMessageFromTemplate($this->getTemplateId()
												, null
												, null
												, null);

		return array( "from" => $this->getFrom()
					, "to" => $a_recipient["name"]." <".$a_recipient["email"].">"
					, "cc" => $this->getCC($a_recipient)
					, "bcc" => $this->getBCC($a_recipient)
					, "subject" => $message["subject"]
					, "message_plain" => $message["plain"]
					, "message_html" => $message["html"]
					, "attachments" => $this->getAttachmentsForMail($a_recipient)
					);
	}
	
	public function getAttachmentsForMail() {
		require_once ("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");

		$material_list_name = gevCrsMailAttachments::MATERIAL_LIST;
		$path = $this->getAttachments()->getPathTo($material_list_name);

		return array( array( "name" => $material_list_name
						   , "path" => $path
						   )
					);
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>