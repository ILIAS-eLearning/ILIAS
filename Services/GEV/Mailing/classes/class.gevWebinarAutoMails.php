<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class gevReminderWebinarMails
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
*/

class gevWebinarAutoMails extends ilAutoMails {
	const MAIL_LOG_ID = 43;
	
	public function __construct($crs_id) {
		$this->mail_data = array(
		  "reminder_webinare"	=> "gevReminderWebinarMail"
		);

		parent::__construct($crs_id);

		global $lng;
		$this->lng = &$lng;
		
		$this->lng->loadLanguageModule("mailing");
	}

	public function getTitle() {
		return "Mails für virtuelle Trainings";
	}

	public function getSubtitle() {
		return "";
	}

	public function getIds() {
		return array_keys($this->mail_data);
	}

	protected function createAutoMail($a_id) {
		if (!array_key_exists($a_id, $this->mail_data)) {
			throw new Exception("Unknown AutoMailID: ".$a_id);
		}

		require_once("./Services/GEV/Mailing/classes/WebinarMails/class.".$this->mail_data[$a_id].".php");
		return new $this->mail_data[$a_id]($a_id, $this->obj_id);
	}

	protected function initMailLog() {
		$this->setMailLog(new ilMailLog($this->obj_id));
	}
}