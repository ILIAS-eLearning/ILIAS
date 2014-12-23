<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';

/**
 * Generali mail data for courses
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @version $Id$
 */

class gevNARegistrationMailData extends ilMailData {
	protected $cache;
	
	public function __construct($a_recipient_id, $a_na_id, $a_confirmation_link, $a_no_confirmation_link) {
		$this->recipient_id = $a_recipient_id;
		$this->na_id = $a_na_id;
		$this->confirmation_link = $a_confirmation_link;
		$this->no_confirmation_link = $a_no_confirmation_link;
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
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		/*if (array_key_exists($a_placeholder_code, $this->cache)) {
			return $this->cache[$a_placeholder_code];
		}*/
		
		$val = null;
		$utils = gevUserUtils::getInstance($this->na_id);
		
		switch ($a_placeholder_code) {
			case "NA-VORNAME":
				$val = $utils->getFirstname();
				break;
			case "NA-NACHNAME":
				$val = $utils->getLastname();
				break;
			case "BESTAETIGUNGSLINK":
				$val = $this->confirmation_link;
				break;
			case "ABLEHNUNGSLINK":
				$val = $this->no_confirmation_link;
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
		return $this->recipient_id;
	}
	
	function deliversStandardPlaceholders() {
		return false;
	}
}

?>