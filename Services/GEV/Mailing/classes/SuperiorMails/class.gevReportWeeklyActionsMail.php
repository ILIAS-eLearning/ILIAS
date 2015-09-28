<?php

require_once("Services/GEV/Mailing/classes/class.gevOrguSuperiorMail.php");

class gevReportWeeklyActionsMail extends gevOrguSuperiorMail {
	public function getTitle() {
		return "Wochenbericht Vorgesetzter";
	}
	
	public function _getDescription() {
		return "Der Vorgesetzte erhält einen Wochenbericht über ausgeführte Aktionen für die Ihm zugewiesenen Mitarbeiter bzw. Vertriebspartner.";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "S01";
	}

	public function getMail($a_recipient) {
		return $this->getMessage($this->getTemplateId(), $a_recipient);
	}

	public function getMessage($a_template_id, $a_recipient) {
		$message = $this->getMessageFromTemplate($a_template_id
												, $a_recipient);

		return array( "from" => $this->getFrom()
					, "to" => $this->getTo($a_recipient)
					, "cc" => $this->getCC($a_recipient)
					, "bcc" => $this->getBCC($a_recipient)
					, "subject" => $message["subject"]?$message["subject"]:""
					, "message_plain" => str_replace("<br />", "\n", $message["plain"])
					, "message_html" => $message["html"]
					, "attachments" => array()
					, "frame_plain" => $this->template_frame->getPlainTextFrame()
					, "frame_html" => $this->template_frame->getHtmlFrame()
					, "image_path" => $this->template_frame->getFileSystemBasePath()."/"
									  .$this->template_frame->getImageName()
					, "image_styles" => $this->template_frame->getImageStyles()
					);
	}

	// Turn template to mail content. Returns
	// a dict containing fields "subject", "plain" and "html"
	protected function getMessageFromTemplate($a_templ_id, $a_recipient, $a_orgunit_obj_id) {
		$this->initTemplateObjects($a_templ_id, "de");
		$rec_name = $this->getNameForTemplate($a_recipient);
		$rec_gender = $this->getGenderForTemplate($a_recipient);

		require_once "./Services/GEV/Mailing/classes/class.gevOrguSuperiorMailData.php";
		$mail_data = new gevOrguSuperiorMailData($a_recipient,$rec_name,$rec_gender);

		$adapter = $this->template_settings->getAdapterClassInstance();

		$placeholders = $adapter->getPlaceholdersLocalized();
		return $this->template_api->getPopulatedVariantMessages($this->template_variant
															   , $placeholders
															   , $mail_data
															   , "de");
	}
}
?>