<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class gevDecentralTrainingsAutoMails
*
* Mails for the creation of decentral trainings.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevDecentralTrainingsAutoMails extends ilAutoMails {
	public function __construct(gevDecentralTrainingCreationRequest $a_request) {
		$this->mail_data = array(
			  "success"		=> "gevDecentralTrainingCreationSuccessMail"
			, "failure"		=> "gevDecentralTrainingCreationFailureMail"
			);
		
		parent::__construct(null);

		global $lng;
		$this->lng = &$lng;
	
		$this->lng->loadLanguageModule("mailing");
		$this->request = $a_request;
	}

	public function getTitle() {
		return "Automatische Mails für Anlage von dezentralen Trainings";
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
		require_once("./Services/GEV/DecentralTrainings/classes/class.".$this->mail_data[$a_id].".php");
		return new $this->mail_data[$a_id]($this->request, $a_id);
	}
	
	protected function initMailLog() {
		$this->setMailLog(new ilMailLog(-1));
	}
}