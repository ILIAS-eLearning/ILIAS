<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class vfRegistrationAutoMails
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevRegistrationMails extends ilAutoMails {
	public function __construct($a_link, $a_token) {
		$this->mail_data = array(
		  "agent_activation"	=> "gevAgentActivationMail"
		);
		$this->token = $a_token;
		$this->link = $a_link;

		parent::__construct(null);

		global $lng;
		$this->lng = &$lng;
		
		$this->lng->loadLanguageModule("mailing");
	}

	public function getTitle() {
		return "Maklerregistrierungsmails";
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
		
		require_once("./Services/GEV/Mailing/classes/RegistrationMails/class.".$this->mail_data[$a_id].".php");
		return new $this->mail_data[$a_id]($this->token, $this->link, $a_id);
	}
	
	protected function initMailLog() {
		$this->setMailLog(new ilMailLog(-1));
	}
}