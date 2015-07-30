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
		$this->recipeint = $a_recipient;
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

		//test date
		$this->end_date_str = "2015-08-03";
		$end_date = new DateTime($this->end_date_str." 23:59:59");
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
				//return "bla";
				return $this->getReportDataString($a_markup);
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

	function getReportDataString($a_markup) {
		$user_data = $this->getReportData();

		$ret = "\n\n<h3>Buchungen:</h3>\n\n";
		$ret .= $this->getFullInfoEachUser($user_data["gebucht"],"Keine Buchungen gefunden.");

		$ret .= "\n\n<h3>Buchungen auf Warteliste:</h3>\n\n";
		$ret .= $this->getFullInfoEachUser($user_data["auf_Warteliste"],"Keine Buchungen auf Warteliste gefunden.");

		$ret .= "\n\n<h3>kostenfreie Stornierungen:</h3>\n\n";
		$ret .= $this->getSmallInfoEachUser($user_data["kostenfrei_storniert"],"Keine kostenfreie Stornierungen gefunden.");

		$ret .= "\n\n<h3>kostenpflichtige Stornierungen:</h3>\n\n";
		$ret .= $this->getSmallInfoEachUser($user_data["kostenpflichtig_storniert"],"Keine kostenpflichtige Stornierungen gefunden.");
		
		$ret .= "\n\n<h3>erfolgreiche Teilnahmen:</h3>\n\n";
		$ret .= $this->getSmallInfoEachUser($user_data["teilgenommen"],"Keine erfolgreiche Teilnahmen gefunden.");

		$ret .= "\n\n<h3>unentschuldigtes Fehlen:</h3>\n\n";
		$ret .= $this->getSmallInfoEachUser($user_data["fehlt_ohne_Absage"],"Keine erfolgreiche Teilnahmen gefunden.");

		if(!$a_markup) {
			$ret = strip_tags($ret);
		}

		return $ret;
	}

	private function getFullInfoEachUser($a_user_data, $a_empty_message) {
		$ret = "";

		if(empty($a_user_data)) {
			return $a_empty_message;
		}

		foreach($a_user_data as $key => $entry) {
			$ret .= "Mitarbeiter/Vertriebspartner: ".$entry["firstname"]." ".$entry["lastname"]."<br />\n";
			$ret .= "Kursinformationen: ".$entry["title"].", ".$entry["type"].", ".$entry["begin_date"]." - ".$entry["end_date"]."<br />\n";
			$ret .= "Übernachtungen: ".$entry["overnights"]."<br />\n";
			
			$prenight = ($entry["prenight"]) ? "Ja" : "Nein";
			$ret .= "Vorabendanreise: ".$prenight."<br />\n";

			$lastnight = ($entry["lastnight"]) ? "Ja" : "Nein";
			$ret .= "Abreise am Folgetag: ".$lastnight."<br />\n<br />\n";
		}

		return $ret;
	}

	private function getSmallInfoEachUser($a_user_data, $a_empty_message) {
		$ret = "";

		if(empty($a_user_data)) {
			return $a_empty_message;
		}
		
		foreach($a_user_data as $key => $entry) {
			$ret .= "Mitarbeiter/Vertiebspartner: ".$entry["firstname"]." ".$entry["lastname"]."<br />\n";
			$ret .= "Kursinformationen: ".$entry["title"].", ".$entry["type"].", ".$entry["begin_date"]." - ".$entry["end_date"]."<br />\n<br />\n";
		}

		return $ret;
	}

	function getReportData() {
		return $this->usr_utils->getUserDataForSuperiorWeeklyReport($this->getStartTimestamp(), $this->getEndTimestamp());
	}
}
?>