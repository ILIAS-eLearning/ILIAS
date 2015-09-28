<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class gevOrguSuperiorMails
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
*/

class gevOrguSuperiorMails extends ilAutoMails {
	const MAIL_LOG_ID = -42;
	
	public function __construct() {
		$this->mail_data = array(
		  "report_weekly_actions"	=> "gevReportWeeklyActionsMail"
		);

		parent::__construct(null);

		global $lng;
		$this->lng = &$lng;
		
		$this->lng->loadLanguageModule("mailing");
	}

	public function getTitle() {
		return "Führungskräftemails";
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
		
		require_once("./Services/GEV/Mailing/classes/SuperiorMails/class.".$this->mail_data[$a_id].".php");
		return new $this->mail_data[$a_id]($a_id);
	}

	protected function initMailLog() {
		$this->setMailLog(new ilMailLog(self::MAIL_LOG_ID));
	}
}