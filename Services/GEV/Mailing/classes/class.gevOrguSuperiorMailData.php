<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailData.php';
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");


/**
 * Generali mail data for Orgunit Superiors
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 */

class gevOrguSuperiorMailData extends ilMailData {
	protected $cache;
	
	public function __construct($a_recipient,$a_rec_name,$a_gender) {
		$this->usr_utils = gevUserUtils::getInstance($a_recipient);
		$this->start_timestamp = null;
		$this->end_timestamp = null;
		$this->end_date_str = "";
		$this->firstname = $a_rec_name["firstname"];
		$this->lastname = $a_rec_name["lastname"];
		$this->gender = $a_gender;
	}
	
	function getRecipientMailAddress() {
		return null;
	}
	function getRecipientFullName() {
		return null;
	}

	function getStartTimestamp() {
		if($this->start_timestamp === null) {
			if($this->end_date_str == "") {
				$this->createEndTimestamp();
			}

			$start_date = new DateTime($this->end_date_str);
			$start_date->sub(date_interval_create_from_date_string('7 Days'));
			$this->start_timestamp = $start_date->getTimestamp();
		}

		return $this->start_timestamp;
	}

	function getEndTimestamp() {
		if($this->end_timestamp === null) {
			$this->createEndTimestamp();
		}

		return $this->end_timestamp;
	}

	function createEndTimestamp() {
		$timestamp_today = time();
		$this->end_date_str = date("Y-m-d", $timestamp_today);
		$end_date = new DateTime($this->end_date_str." 23:59:59");

		if(date("l",$timestamp_today) == "Monday") {
			$end_date->sub(date_interval_create_from_date_string('1 Day'));
			$this->end_date_str = $end_date->format("Y-m-d");
		}

		$this->end_timestamp = $end_date->getTimestamp();
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
			case "BERICHT":
				$val = $this->getReportDataString();
				break;
		}
		
		if ($val === null) {
			$val = $a_placeholder_code;
		}
		
		$this->cache[$a_placeholder_code] = $val;
		
		if(!$a_markup) {
			$val = strip_tags($val);
		}
		
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
				$tpl->setVariable("CRS_TYPE", $entry_data["type"]);
				$begin_date = new ilDate($entry_data["begin_date"], IL_CAL_DATE);
				$end_date = new ilDate($entry_data["end_date"], IL_CAL_DATE);
				$date = ilDatePresentation::formatPeriod($begin_date, $end_date);
				$tpl->setVariable("CRS_DATE", $date);
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
			throw new Exception("There is no content in the weakly report for the superior.");
		}

		return $ret;
	}


	function getReportData() {
		return $this->usr_utils->getUserDataForSuperiorWeeklyReport($this->getStartTimestamp(), $this->getEndTimestamp());
	}
	
	function getTemplate() {
		require_once("Services/UICore/classes/class.ilTemplate.php");
		return new ilTemplate("tpl.superior_mail.html", true, true, "Services/GEV/Mailing");
	}
}
?>