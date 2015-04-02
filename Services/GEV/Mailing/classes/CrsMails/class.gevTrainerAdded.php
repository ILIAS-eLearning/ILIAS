<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevTrainerAdded extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Trainer";
	}
	
	public function _getDescription() {
		return "Trainer wird auf Training hinzugefügt";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B07";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseTrainers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
	
	public function getMail($a_recipient) {
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

		$ical = gevCrsMailAttachments::ICAL_ENTRY;
		$path = $this->getAttachments()->getPathTo($ical);

		return array( array( "name" => $ical
						   , "path" => $path
						   )
					);
	}
}

?>