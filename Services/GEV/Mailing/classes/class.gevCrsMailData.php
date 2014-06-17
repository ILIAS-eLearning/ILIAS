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

class gevCrsMailData extends ilMailData {
	protected $rec_email;
	protected $rec_fullname;
	protected $rec_user_id;
	protected $crs_utils;
	protected $usr_utils;
	protected $cache;
	
	public function __construct() {
		$this->crs_utils = null;
		$this->usr_utils = null;
	}
	
	function getRecipientMailAddress() {
		return $this->rec_email;
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
		if (  $this->crs_utils === null
		   || $this->usr_utils === null) {
			throw new Exception("gevCrsMailData::getPlaceholderLocalized: course or user utilities not initialized.");
		}
		
		if (array_key_exists($a_placeholder_code, $this->cache)) {
			return $this->cache[$a_placeholder_code];
		}
		
		ilDatePresentation::setUseRelativeDates(false);
		
		switch ($a_placeholder_code) {
			case "TRAININGSTITEL":
				$val = $this->crs_utils->getTitle();
				break;
			case "TRAININGSUNTERTITEL":
				$val = $this->crs_utils->getSubtitle();
				break;
			case "LERNART":
				$val = $this->crs_utils->getType();
				break;
			case "TRAININGSTHEMEN":
				$val = implode(", ", $this->crs_utils->getTopics());
				break;
			case "WP":
				$val = $this->crs_utils->getCreditPoints();
				break;
			case "METHODEN":
				$val = implode(", ", $this->crs_utils->getMethods());
				break;
			case "MEDIEN":
				$val = implode(", ", $this->crs_utils->getMedia());
				break;
			case "Zielgruppen":
				$val = implode(", ", $this->crs_utils->getTargetGroup());
				break;
			case "Inhalt":
				$val = $this->crs_utils->getContents();
				if (!$a_markup) {
					$val = strip_tags($val);
				}
				break;
			case "ID":
				$val = $this->crs_utils->getCustomId();
				break;
			case "Startdatum":
				$val =$this->crs_utils->getFormattedStartDate();
				break;
			//case "Startzeit"				, "Uhrzeit des Beginns des Trainings")
			//case "Enddatum"				, "Enddatum des Trainings")
			//case "Endzeit"				, "Uhrzteit des Ende des Trainings")
			//case "TV-Name"				, "Name des Themenverantwortlichen des Trainings")
			//case "TV-Telefon"			, "Telefonnummer des Themenverantwortlichen")
			//case "TV-Email"				, "Emailadresse des Themenverantwortlichen")
			//case "Admin-Name"			, "Name des Trainingsadministrator")
			//case "Admin-Telefon"			, "Telefonnummer des Trainingsadministrators")
			//case "Admin-Email"			, "Emailadresse des Trainingsadministrators")
			//case "Trainer-Name"			, "Name des Trainers")
			//case "Trainer-Telefon"		, "Telefonnummer des Trainers")
			//case "Trainer-Email"			, "Email des Trainers")
			//case "VO-Name"				, "Name des Veranstaltungsorts des Trainings")
			//case "VO-Straße"				, "Straße des Veranstaltungsorts")
			//case "VO-Hausnummer"			, "Hausnummer des Veranstaltungsorts")
			//case "VO-PLZ"				, "Postleitzahl des Veranstaltungsorts")
			//case "VO-Ort"				, "Ort des Veranstaltungsorts")
			//case "Hotel-Name"			, "Name des Übernachtungsorts des Trainings")
			//case "Hotel-Straße"			, "Straße des Übernachtungsorts")
			//case "Hotel-Hausnummer"		, "Hausnummer des Übernachtungsorts")
			//case "Hotel-PLZ"				, "Postleitzahl des Übernachtungsorts")
			//case "Hotel-Ort"				, "Ort des Übernachtungsorts")
			//case "Hotel-Telefon"			, "Telefonnummer des Übernachtungsorts")
			//case "Hotel-Email"			, "Emailadresse des Übernachtungsorts")
		}
		
		$this->cache[$a_placeholder_code] = $val;
		return $val;
	}

	// Phase 2: Attachments via Maildata
	function hasAttachments() {
		return false;
	}
	function getAttachments($a_lng) {
		return array();
	}
	
	function getRecipientUserId() {
		return $this->rec_user_id;
	}
	
	function initCourseData(gevCourseUtils $a_crs) {
		$this->cache = array();
		$this->crs_utils = $a_crs;
	}
	function setRecipient($a_user_id, $a_email, $a_name) {
		$this->cache = array();
		$this->rec_user_id = $a_user_id;
		$this->rec_email = $a_email;
		$this->rec_fullname =$a_fullname;
	}
	function initUserData(gevUserUtils $a_usr) {
		$this->cache = array();
		$this->usr_utils = $a_usr;
	}
}

?>