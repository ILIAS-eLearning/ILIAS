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
		if (  $this->crs_utils === null) {
			throw new Exception("gevCrsMailData::getPlaceholderLocalized: course utilities not initialized.");
		}
		
		if (array_key_exists($a_placeholder_code, $this->cache)) {
			return $this->cache[$a_placeholder_code];
		}
		
		$val = null;
		
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
				$methods = $this->crs_utils->getMethods();
				if ($methods !== null) {
					$val = implode(", ", $methods);
				}
				else {
					$val = "";
				}
				break;
			case "MEDIEN":
				$media = $this->crs_utils->getMedia();
				if ($media !== null) {
					$val = implode(", ", $media);
				}
				else {
					$val = "";
				}
				break;
			case "ZIELGRUPPEN":
				$target_group =  $this->crs_utils->getTargetGroup();
				if ($target_group !== null) {
					$val = implode(", ", $target_group);
				}
				else {
					$val = "";
				}
				break;
			case "INHALT":
				$val = $this->crs_utils->getContents();
				if (!$a_markup) {
					$val = strip_tags($val);
				}
				break;
			case "ZIELE UND NUTZEN":
				$val = $this->crs_utils->getGoals();
				if (!$a_markup) {
					$val = strip_tags($val);
				}
				break;
			case "ID":
				$val = $this->crs_utils->getCustomId();
				break;
			case "STARTDATUM":
				$val = $this->crs_utils->getFormattedStartDate();
				break;
			case "STARTZEIT":
				$val = $this->crs_utils->getFormattedStartTime();
				break;
			case "ENDDATUM":
				$val = $this->crs_utils->getFormattedEndDate();
				break;
			case "ENDZEIT":
				$val = $this->crs_utils->getFormattedEndTime();
				break;
			case "ZEITPLAN":
				$val = $this->crs_utils->getFormattedSchedule();
				break;
			case "TV-NAME":
				$val = $this->crs_utils->getTrainingOfficerName();
				break;
			case "TV-TELEFON":
				$val = $this->crs_utils->getTrainingOfficerPhone();
				break;
			case "TV-EMAIL":
				$val = $this->crs_utils->getTrainingOfficerEmail();
				break;
			case "TRAININGSBETREUER-VORNAME":
				$val = $this->crs_utils->getMainAdminFirstname();
				break;
			case "TRAININGSBETREUER-NACHNAME":
				$val = $this->crs_utils->getMainAdminLastname();
				break;
			case "TRAININGSBETREUER-TELEFON":
				$val = $this->crs_utils->getMainAdminPhone();
				break;
			case "TRAININGSBETREUER-EMAIL":
				$val = $this->crs_utils->getMainAdminEmail();
				break;
			case "TRAINER-NAME":
				$val = $this->crs_utils->getMainTrainerName();
				break;
			case "TRAINER-TELEFON":
				$val = $this->crs_utils->getMainTrainerPhone();
				break;
			case "TRAINER-EMAIL":
				$val = $this->crs_utils->getMainTrainerEmail();
				break;
			case "VO-NAME":
				$val = $this->crs_utils->getVenueTitle();
				break;
			case "VO-STRAßE":
				$val = $this->crs_utils->getVenueStreet();
				break;
			case "VO-HAUSNUMMER":
				$val = $this->crs_utils->getVenueHouseNumber();
				break;
			case "VO-PLZ":
				$val = $this->crs_utils->getVenueZipcode();
				break;
			case "VO-ORT":
				$val = $this->crs_utils->getVenueCity();
				break;
			case "VO-TELEFON":
				$val = $this->crs_utils->getVenuePhone();
				break;
			case "WEBEX-LINK":
				$val = $this->crs_utils->getWebExLink();
				break;
			case "WEBEX-PASSWORT":
				$val = $this->crs_utils->getWebExPassword();
				break;
			case "CSN-LINK":
				$val = $this->crs_utils->getCSNLink();
				break;
			case "HOTEL-NAME":
				$val = $this->crs_utils->getAccomodationTitle();
				break;
			case "HOTEL-STRAßE":
				$val = $this->crs_utils->getAccomodationStreet();
				break;
			case "HOTEL-HAUSNUMMER":
				$val = $this->crs_utils->getAccomodationHouseNumber();
				break;
			case "HOTEL-PLZ":
				$val = $this->crs_utils->getAccomodationZipcode();
				break;
			case "HOTEL-ORT":
				$val = $this->crs_utils->getAccomodationCity();
				break;
			case "HOTEL-TELEFON":
				$val = $this->crs_utils->getAccomodationPhone();
				break;
			case "HOTEL-EMAIL":
				$val = $this->crs_utils->getAccomodationEmail();
				break;
			case "BUCHENDER_VORNAME":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getFirstnameOfUserWhoBookedAtCourse($this->crs_utils->getId());
				}
				break;
			case "BUCHENDER_NACHNAME":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getLastnameOfUserWhoBookedAtCourse($this->crs_utils->getId());
				}
				break;
			case "EINSATZTAGE":
				$start = $this->crs_utils->getStartDate();
				$end = $this->crs_utils->getEndDate();
				
				if ($start && $end) {
					require_once("Services/TEP/classes/class.ilTEPOperationDays.php");
					$tmp = new ilTEPOperationDays("crs", $this->crs_utils->getId(), $start, $end);
					$op_days = $tmp->getDaysForUser($this->rec_user_id);
					$val = implode("", $op_days);
				}
				else {
					$val = "Nicht verfügbar.";
				}
				break;
			case "UEBERNACHTUNGEN":
				if ($this->usr_utils !== null) {
					$tmp = $this->usr_utils->getOvernightDetailsForCourse($this->crs_utils->getCourse());
					$dates = array();
					foreach ($tmp as $date) {
						$d = ilDatePresentation::formatDate($date);
						$date->increment(ilDateTime::DAY, 1);
						$d .= " - ".ilDatePresentation::formatDate($date); 
						$dates[] = $d;
					}
					$val = implode("<br />", $dates);
				}
				break;
			case "ORGANISATORISCHES":
				$val = $this->crs_utils->getOrgaInfo();
				break;
			//case "LISTE":
			//	break;
		}
		
		if ($val === null) {
			$val = $a_placeholder_code;
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