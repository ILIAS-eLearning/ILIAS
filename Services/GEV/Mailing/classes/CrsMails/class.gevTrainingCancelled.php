<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevTrainingCancelled extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Hotel";
	}
	
	public function _getDescription() {
		return "Training wird vom Admin storniert";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C08";
	}
	
	public function getUsersOnly() {
		return false;
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseHotelAddresses();
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
		
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
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

		$member_list_name = gevCrsMailAttachments::LIST_FOR_HOTEL_NAME;
		$path = $this->getAttachments()->getPathTo($member_list_name);

		$message = $this->getMessageFromTemplate($this->getTemplateId(), null, $a_recipient["email"], $a_recipient["name"]);

		return array( array( "name" => $member_list_name
						   , "path" => $path
						   )
					);
	}
}

?>