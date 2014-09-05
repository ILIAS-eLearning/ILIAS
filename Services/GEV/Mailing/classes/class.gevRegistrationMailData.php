<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

/**
 * Generali mail data for courses
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

class gevRegistrationMailData extends ilMailData {
	protected $cache;
	
	public function __construct($link, $email, $firstname, $lastname, $gender, $login) {
		$this->link = $link;
		$this->email = $email;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->gender = $gender;
		$this->login = $login;
	}
	
	function getRecipientMailAddress() {
		return $this->email;
	}
	function getRecipientFullName() {
		return $this->rec_fullname;
	}
	
	function hasCarbonCopyRecipients() {
		return false;
	}
	
	function getCarbonCopyRecipients() {
		return array();
	}
	
	function hasBlindCarbonCopyRecipients() {
		return false;
	}
	
	function getBlindCarbonCopyRecipients() {
		return array();
	}
	
	function getPlaceholderLocalized($a_placeholder_code, $a_lng, $a_markup = false) {
		if (array_key_exists($a_placeholder_code, $this->cache)) {
			return $this->cache[$a_placeholder_code];
		}
		
		$val = null;
		
		switch ($a_placeholder_code) {
			case "SALUTATION":
				if ($this->gender == "m") {
					$val = "Sehr geehrter Herr";
				}
				else {
					$val = "Sehr geehrte Frau";
				}
				break;
			case "LOGIN":
				$val = $this->login;
				break;
			case "FIRST_NAME":
				$val = $this->firstname;
				break;
			case "LAST_NAME":
				$val = $this->lastname;
				break;
			case "AKTIVIERUNGSLINK":
				$link = $this->link;
				$val = "<a href='".$link."'>".$link."</a>";
				break;
		}
		
		if ($val === null) {
			$val = $a_placeholder_code;
		}
		
		$this->cache[$a_placeholder_code] = $val;
		return $val;
	}

	function hasAttachments() {
		return false;
	}
	function getAttachments($a_lng) {
		return array();
	}
	
	function getRecipientUserId() {
		return null;
	}
	
	function deliversStandardPlaceholders() {
		return true;
	}
}

?>