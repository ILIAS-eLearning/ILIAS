<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilAutoMails.php");
require_once("Services/Mailing/classes/class.ilMailLog.php");

/**
* Class vfRegistrationAutoMails
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevNARegistrationMails extends ilAutoMails {
	public function __construct($a_na_firstname, $a_na_lastname, $a_confirmation_link, $a_no_confirmation_link) {
		$this->mail_data = array(
		  "na_confirmation"		=> "gevNAConfirmationMail"
		, "na_confirmed"		=> "gevNAConfirmedMail"
		, "na_not_confirmed"	=> "gevNANotConfirmedMail"
		);
		$this->na_firstname = $a_na_firstname;
		$this->na_lastname = $a_na_lastname;
		$this->confirmation_link = $a_confirmation_link;
		$this->no_conformation_link = $a_no_confirmation_link;

		parent::__construct(null);

		global $lng;
		$this->lng = &$lng;
		
		$this->lng->loadLanguageModule("mailing");
	}

	public function getTitle() {
		return "NA-Registrierungsmails";
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
		return new $this->mail_data[$a_id]($this->na_firstname, $this->na_lastname
										  , $this->confirmation_link, $this->no_conformation_link);
	}
	
	protected function initMailLog() {
		$this->setMailLog(new ilMailLog(-1));
	}
}