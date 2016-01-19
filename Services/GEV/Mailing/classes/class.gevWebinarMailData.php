<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");


/**
 * Generali mail data for Webinar Reminder
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 */

class gevWebinarMailData extends ilMailData {
	protected $cache;
	
	public function __construct($a_recipient,$a_gender) {
		$this->usr_utils = gevUserUtils::getInstance($a_recipient);
		$this->start_timestamp = null;
		$this->end_timestamp = null;
		$this->end_date_str = "";
		$this->gender = $a_gender;
	}
	
	function getRecipientMailAddress() {
		return null;
	}
	function getRecipientFullName() {
		return null;
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
	
	function getPlaceholderLocalized($a_placeholder_code, $a_lng = "de", $a_markup = false) {
		if (  $this->crs_utils === null) {
			throw new Exception("gevCrsMailData::getPlaceholderLocalized: course utilities not initialized.");
		}
		
		$val = null;
		
		switch ($a_placeholder_code) {
			case "MOBIL":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getMobilePhone();
				}
				break;
			case "OD":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getOD();
					$val = $val["title"];
				}
				break;
			case "VERMITTLERNUMMER":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getJobNumber();
				}
				break;
			case "ADP GEV":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getADPNumberGEV();
				}
				break;
			case "ADP VFS":
				if ($this->usr_utils !== null) {
					$val = $this->usr_utils->getADPNumberVFS();
				}
				break;
			case "TRAININGSTITEL":
				$val = $this->crs_utils->getTitle();
				break;
			case "TRAININGSUNTERTITEL":
				$val = $this->crs_utils->getSubtitle();
				break;
			case "LERNART":
			case "TRAININGSTYP":
				$val = $this->crs_utils->getType();
				break;
			case "TRAININGSTHEMEN":
				if(!empty($this->crs_utils->getTopics())) {
					$val = implode(", ", $this->crs_utils->getTopics());
				} else {
					$val = "";
				}
				
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
			case "TRAININGSERSTELLER-VORNAME":
				$val = $this->crs_utils->getMainTrainingCreatorFirstname();
				break;
			case "TRAININGSERSTELLER-NACHNAME":
				$val = $this->crs_utils->getMainTrainingCreatorLastname();
				break;
			case "TRAININGSERSTELLER-TELEFON":
				$val = $this->crs_utils->getMainTrainingCreatorPhone();
				break;
			case "TRAININGSERSTELLER-EMAIL":
				$val = $this->crs_utils->getMainTrainingCreatorEmail();
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
			case "ALLE TRAINER":
				$trainers = $this->crs_utils->getTrainers();
				$val = array();
				foreach ($trainers as $trainer) {
					$utils = gevUserUtils::getInstance($trainer);
					$val[] = $utils->getFormattedContactInfo();
				}
				$val = implode("<br />", $val);
				break;
			case "VO-NAME":
				$val = $this->crs_utils->getVenueTitle();
				if (!$val) {
					return $this->crs_utils->getVenueFreeText();
				}
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
			case "VO-INTERNET":
				$val = $this->crs_utils->getVenueHomepage();
				break;
			case "WEBINAR-LINK":
				$val = $this->crs_utils->getVirtualClassLinkWithHTTP();
				break;
			case "WEBINAR-PASSWORT":
				$val = $this->crs_utils->getVirtualClassPassword();
				break;
			case "WEBINAR-LOGIN-TRAINER":
				$val = $this->crs_utils->getVirtualClassLoginTutor();
				break;
			case "WEBINAR-PASSWORT-TRAINER":
				$val = $this->crs_utils->getVirtualClassPasswordTutor();
				break;
			/*case "CSN-LINK":
				$val = $this->crs_utils->getCSNLink();
				break;*/
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
					try {
						require_once("Services/TEP/classes/class.ilTEPCourseEntries.php");
						$tmp = ilTEPCourseEntries::getInstance($this->crs_utils->getCourse())
									->getOperationsDaysInstance();
						$op_days = $tmp->getDaysForUser($this->rec_user_id);
						foreach ($op_days as $key => $value) {
							$op_days[$key] = ilDatePresentation::formatDate($value);
						}
						$val = implode("<br />", $op_days);
					}
					catch (ilTEPException $e) {
						$val = "Nicht verfügbar.";
					}
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
			case "VORABENDANREISE":
				if ($this->usr_utils !== null) {
					$tmp = $this->usr_utils->getOvernightDetailsForCourse($this->crs_utils->getCourse());
					$start_date = $this->crs_utils->getStartDate();
					if (   count($tmp) > 0 && $start_date
						&& $tmp[0]->get(IL_CAL_DATE) < $start_date->get(IL_CAL_DATE)) {
						$val = "Ja";
					}
					else {
						$val = "Nein";
					}
				}
				break;
			case "NACHTAGABREISE":
				if ($this->usr_utils !== null) {
					$tmp = $this->usr_utils->getOvernightDetailsForCourse($this->crs_utils->getCourse());
					$end_date = $this->crs_utils->getEndDate();
					if (   count($tmp) > 0 && $end_date
						&& $tmp[count($tmp)-1]->get(IL_CAL_DATE) == $end_date->get(IL_CAL_DATE)) {
						$val = "Ja";
					}
					else {
						$val = "Nein";
					}
				}
				break;
			case "ORGANISATORISCHES":
				$val = $this->crs_utils->getOrgaInfo();
				break;
			case "LISTE":
				$l = $this->crs_utils->getParticipants();
				$names = array();
				foreach ($l as $user_id) {
					$names[] = ilObjUser::_lookupFullname($user_id);
				}
				$val = implode("<br />", $names);
				break;
			default:
				return $a_placeholder_code;
		}
		
		$val = $this->maybeFormatEmptyField($val);
		if (!$a_markup) 
			$val = str_replace("<br />", "\n", $val);
		
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

	function getReportDataString() {
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		require_once("Services/UICore/classes/class.ilTemplateHTMLITX.php");
		require_once("Services/PEAR/lib/HTML/Template/ITX.php");
		require_once("Services/PEAR/lib/HTML/Template/IT.php");
		
		$user_data = $this->getReportData();
		
		$show_sections = array
			( "gebucht" => "Buchungen"
			, "kostenfrei_storniert" => "Kostenfreie Stornierungen"
			, "kostenpflichtig_storniert" => "Kostenpflichtige Stornierungen"
			, "teilgenommen" => "Erfolgreiche Teilnahmen"
			, "fehlt_ohne_Absage" => "Unentschuldigtes Fehlen"
			);

		$ret = "";

		$has_entries = false;

		foreach ($show_sections as $key => $title) {
			
			$section_data = $user_data[$key];
			if (count($section_data) <= 0) {
				continue;
			}
			
			$tpl = $this->getTemplate();
			$tpl->setCurrentBlock("header");
			$tpl->setVariable("TITLE", $title);
			$tpl->parseCurrentBlock();
			$ret .= $tpl->get();

			foreach ($section_data as $entry_data) {
				$has_entries = true;
				$tpl = $this->getTemplate();
				$tpl->setCurrentBlock("entry");
				$tpl->setVariable("USR_FIRSTNAME", $entry_data["firstname"]);
				$tpl->setVariable("USR_LASTNAME", $entry_data["lastname"]);
				$tpl->setVariable("CRS_TITLE", $entry_data["title"]);
				$tpl->setVariable("CRS_TYPE", $this->mergeEduProgramAndType($entry_data["edu_program"], $entry_data["type"]));
				
				if ($begin_date != "0000-00-00") {
					$begin_date = new ilDate($entry_data["begin_date"], IL_CAL_DATE);
				}
				else {
					$begin_date = null;
				}

				if ($end_date != "0000-00-00") {
					$end_date = new ilDate($entry_data["end_date"], IL_CAL_DATE);
				}
				else {
					if ($begin_date !== null) {
						$end_date = $begin_date;
					}
					else {
						$end_date = null;
					}
				}
				
				if ($end_date !== null && $begin_date !== null && $entry_data["type"] !== "Selbstlernkurs") {
					$date = ilDatePresentation::formatPeriod($begin_date, $end_date);
					$tpl->setVariable("CRS_DATE", ", $date");
				}

				if ((!in_array($entry_data["type"], array("Selbstlernkurs", "Webinar", "Virtuelles Training"))) && $key == "gebucht") {
					$tpl->setCurrentBlock("overnights");
					$tpl->setVariable("OVERNIGHTS_CAPTION", "Übernachtungen");
					$tpl->setVariable("USR_OVERNIGHTS_AMOUNT", $entry_data["overnights"]);
					$tpl->setVariable("PREARRIVAL_CAPTION", "Vorabendanreise");
					$tpl->setVariable("USR_HAS_PREARRIVAL", $entry_data["prearrival"] ? "Ja" : "Nein");
					$tpl->setVariable("POSTDEPARTURE_CAPTION", "Abreise am Folgetag");
					$tpl->setVariable("USR_HAS_POSTDEPARTURE", $entry_data["postdeparture"] ? "Ja" : "Nein");
					$tpl->parseCurrentBlock();
				}
				$tpl->parseCurrentBlock();
				$ret .= $tpl->get();
			}
		}
		
		if (!$has_entries) {
			throw new Exception("There is no content in the weekly report for the superior.");
		}

		return $ret;
	}

	// This implements the requirement to output the type of the program and
	// the edu program together (#1689), e.g. "Präsenztraining" from "zentrales
	// Training" should be displayed as "zentrales Präsenztraining" 
	function mergeEduProgramAndType($a_edu_program, $a_type) {
		if (!in_array($a_type, array("Webinar", "Präsenztraining"))) {
			return $a_type;
		}
		
		switch ($a_edu_program) {
			case "zentrales Training":
				return "zentrales $a_type";
			case "dezentrales Training":
				return "dezentrales $a_type";
			case "Grundausbildung":
				return $a_type." (Grundausbildung)";
			case "Azubi-Ausbildung":
				return $a_type." (Azubi-Ausbildung)";
			default:
				return $a_type;
		}
	}

	function getReportData() {
		return $this->usr_utils->getUserDataForSuperiorWeeklyReport($this->getStartTimestamp(), $this->getEndTimestamp());
	}
	
	function getTemplate() {
		require_once("Services/UICore/classes/class.ilTemplate.php");
		return new ilTemplate("tpl.superior_mail.html", true, true, "Services/GEV/Mailing");
	}

	function initCourseData(gevCourseUtils $a_crs) {
		$this->cache = array();
		$this->crs_utils = $a_crs;
	}

	function maybeFormatEmptyField($val) {
		if ($val === null) {
			return "-";
		}
		else {
			return $val;
		}
	}
}