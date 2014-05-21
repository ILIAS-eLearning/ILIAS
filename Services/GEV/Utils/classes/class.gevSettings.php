<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class gevSettings
*
* Get and set settings for the generali. Wrapper around ilSettings.
*
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id$
*/

require_once("Services/Administration/classes/class.ilSetting.php");

class gevSettings {
	static $instance = null;
	static $amd_fields = null;
	
	const MODULE_NAME = "gev";
	
	// vgl. Konzept, Abschnitt Trainingsvorlagen
	
	// Block "Trainingsverwaltung"
	// Nummer der Maßnahme
	const CRS_AMD_CUSTOM_ID 		= "crs_amd_custom_id";
	// Trainingsvorlage (nicht im Konzept)
	const CRS_AMD_TEMPLATE_TITLE	= "crs_amd_template_title";
	//Lernart
	const CRS_AMD_TYPE 				= "crs_amd_type";
	// Trainingsbetreuer -> ILIAS Standard
	//const CRS_AMD_MAINTAINER		= "crs_amd_maintainer";

	// Start- und Enddatum
	const CRS_AMD_START_DATE		= "crs_amd_start_date";
	const CRS_AMD_END_DATE			= "crs_and_end_date";
	
	// Block Trainingsinhalte
	// Trainingsthema
	const CRS_AMD_TOPIC 			= "crs_amd_topic";
	// Inhalte
	const CRS_AMD_CONTENTS 			= "crs_amd_content";
	// Ziele und Nutzen
	const CRS_AMD_GOALS 			= "crs_amd_goals";
	// Methoden
	const CRS_AMD_METHODS 			= "crs_amd_methods";
	// Medien
	const CRS_AMD_MEDIA				= "crs_amd_media";


	// Zielgruppe für Suche
	const CRS_AMD_TARGET_GROUP		= "crs_amd_target_group";
	// Zielgruppenbeschreibung
	const CRS_AMD_TARGET_GROUP_DESC	= "crs_amd_target_group_desc";

	// Fachschulung
	const CRS_AMD_EXPERT_TRAINING	= "crs_amd_expert_training";
	// Bildungspunkte
	const CRS_AMD_CREDIT_POINTS		= "crs_amd_credit_points";
	
	// Teilnahmegebühr
	const CRS_AMD_FEE				= "crs_amd_fee";
	
	// Mindestteilnehmerzahl
	const CRS_AMD_MIN_PARTICIPANTS	= "crs_amd_min_participants";
	// Stornofrist
	const CRS_AMD_CANCEL_DEADLINE	= "crs_amd_cancel_deadline";
	// Buchungsfrist
	const CRS_AMD_BOOKING_DEADLINE	= "crs_amd_booking_deadline";
	// Absage Wartliste
	const CRS_AMD_CANCEL_WAITING	= "crs_amd_cancel_waiting";
	
	// Anbieter
	const CRS_AMD_PROVIDER			= "crs_amd_provider";
	// Veranstaltungsort
	const CRS_AMD_VENUE				= "crs_amd_venue";
	// Übernachtungsort
	const CRS_AMD_ACCOMODATION		= "crs_amd_accomodation";
	
	private function __construct() {
		$this->settings = new ilSetting(self::MODULE_NAME);
	}
	
	public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevSettings();
		}
		
/*		self::$amd_fields = 
			array( CRS_AMD_CUSTOM_ID
				 , CRS_AMD_TEMPLATE_TITLE
				 , CRS_AMD_TYPE
				 , CRS_AMD_TOPIC
				 , CRS_AMD_CONTENTS
				 , CRS_AMD_GOALS
				 , CRS_AMD_METHODS
				 , CRS_AMD_MEDIA
				 , CRS_AMD_TARGET_GROUP
				 , CRS_AMD_TARGET_GROUP_DESC
				 , CRS_AMD_EXPERT_TRAINING
				 , CRS_AMD_CREDIT_POINTS
				 , CRS_AMD_FEE
				 , CRS_AMD_MIN_PARTICIPANTS
				 , CRS_AMD_CANCEL_DEADLINE
				 , CRS_AMD_PROVIDER
				 , CRS_AMD_CANCEL_WAITING
				 , CRS_AMD_VENUE
				 , CRS_AMD_ACCOMODATION
				 );*/
		
		return self::$instance;
	}
	
	public function get($a_field) {
		return $this->settings->get($a_field);
	}
	
	public function set($a_field, $a_value) {
		$this->settings->set($a_field, $a_value);
	}
	
	/*public function isAMDRecordUsed($a_record_id) {
			global $ilDB;
			$res = $ilDB->query("SELECT COUNT(*) AS cnt FROM settings ".
								"    WHERE module = 'gev' ".
								"      AND ".$ilDB->in("keyword", self::$amd_fields))
	}*/
}

?>