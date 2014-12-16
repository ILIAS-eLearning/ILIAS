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
	// Nummernkreis
	const CRS_AMD_CUSTOM_ID_TEMPLATE = "crs_amd_custom_id_template";
	// Trainingsvorlage (nicht im Konzept)
	const CRS_AMD_TEMPLATE_TITLE	= "crs_amd_template_title";
	// Trainingsvorlage Ref-ID (nicht im Konzept)
	const CRS_AMD_TEMPLATE_REF_ID	= "crs_amd_template_ref_id";
	//Lernart
	const CRS_AMD_TYPE 				= "crs_amd_type";
	// Vorlage
	const CRS_AMD_IS_TEMPLATE		= "crs_amd_is_template";
	
	// Trainingsbetreuer -> ILIAS Standard
	//const CRS_AMD_MAINTAINER		= "crs_amd_maintainer";

	// Start- und Enddatum
	const CRS_AMD_START_DATE		= "crs_amd_start_date";
	const CRS_AMD_END_DATE			= "crs_amd_end_date";
	// Zeitplan
	const CRS_AMD_SCHEDULE			= "crs_amd_schedule";
	// geplant für
	const CRS_AMD_SCHEDULED_FOR		= "crs_amd_scheduled_for";
	// Organisatorisches
	const CRS_AMD_ORGA				= "crs_amd_orga";
	
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
	// Bildungsprogramm
	const CRS_AMD_EDU_PROGRAMM		= "crs_amd_edu_program";


	// Zielgruppe für Suche
	const CRS_AMD_TARGET_GROUP		= "crs_amd_target_group";
	// Zielgruppenbeschreibung
	const CRS_AMD_TARGET_GROUP_DESC	= "crs_amd_target_group_desc";

	// Fachschulung
	const CRS_AMD_EXPERT_TRAINING	= "crs_amd_expert_training";
	// Bildungspunkte
	const CRS_AMD_CREDIT_POINTS		= "crs_amd_credit_points";
	// GEV Lerninhalt
	const CRS_AMD_GDV_TOPIC			= "crs_amd_gdv_topic";
	
	// Teilnahmegebühr
	const CRS_AMD_FEE				= "crs_amd_fee";
	// Mice-ID
	const CRS_AMD_MICE_ID			= "crs_amd_mice_id";
	
	// Mindestteilnehmerzahl
	const CRS_AMD_MIN_PARTICIPANTS	= "crs_amd_min_participants";
	// Warteliste
	const CRS_AMD_WAITING_LIST_ACTIVE = "crs_amd_waiting_list_active";
	// Maximalteilnehmerzahl
	const CRS_AMD_MAX_PARTICIPANTS	= "crs_amd_max_participants";
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
	// Veranstaltungsort Internet
	//const CRS_AMD_WEB_LOCATION		= "crs_amd_web_location";
	const CRS_AMD_WEBEX_LINK		= "crs_amd_webex_link";			// these are general webinar links now
	const CRS_AMD_WEBEX_PASSWORD	= "crs_amd_webex_password";		// these are general webinar passwords now
	const CRS_AMD_CSN_LINK			= "crs_amd_csn_link";	// this is not used anymore
	
	// Typen von Organisationseinheiten
	const ORG_TYPE_VENUE			= "org_unit_type_venue";
	const ORG_TYPE_PROVIDER			= "org_unit_type_provider";
	const ORG_TYPE_DEFAULT			= "org_unit_type_default";
	
	static $all_org_types = array( gevSettings::ORG_TYPE_VENUE
								 , gevSettings::ORG_TYPE_PROVIDER
								 , gevSettings::ORG_TYPE_DEFAULT
								 );
	
	// AMD für alle Org-Units (vgl. Konzept, Abschnitte Veranstaltungsorte, Anbieter)
	// Straße
	const ORG_AMD_STREET			= "org_amd_street";
	// Hausnummer
	const ORG_AMD_HOUSE_NUMBER		= "org_amd_house_number";
	// Postleitzahl
	const ORG_AMD_ZIPCODE			= "org_amd_zipcode";
	// Ort
	const ORG_AMD_CITY				= "org_amd_city";
	// Ansprechpartner
	const ORG_AMD_CONTACT_NAME		= "org_amd_contact_name";
	// Telefon
	const ORG_AMD_CONTACT_PHONE		= "org_amd_contact_phone";
	// Fax
	const ORG_AMD_CONTACT_FAX		= "org_amd_contact_fax";
	// eMail
	const ORG_AMD_CONTACT_EMAIL		= "org_amd_contact_email";
	// Homepage
	const ORG_AMD_HOMEPAGE			= "org_amd_homepage";

	// Kostenstelle
	const ORG_AMD_FINANCIAL_ACCOUNT	= "org_amd_financial_account";


	// AMD für Veranstaltungsorte
	// Anfahrt
	const VENUE_AMD_LOCATION		= "venue_amd_location";
	// Kosten je Übernachtung
	const VENUE_AMD_COSTS_PER_ACCOM	= "venue_amd_costs_per_accom";
	// Pauschale Frühstück
	const VENUE_AMD_COSTS_BREAKFAST	= "venue_amd_costs_breakfast";
	// Pauschale Mittagessen
	const VENUE_AMD_COSTS_LUNCH		= "venue_amd_costs_lunch";
	// Nachmittagspauschale
	const VENUE_AMD_COSTS_COFFEE	= "venue_amd_costs_coffee";
	// Pauschale Abendessen
	const VENUE_AMD_COSTS_DINNER	= "venue_amd_costs_dinner";
	// Pauschale Tagesverpflegung
	const VENUE_AMD_COSTS_FOOD		= "venue_amd_costs_food";
	// Vollkostenpauschale Hotel
	const VENUE_AMD_COSTS_HOTEL		= "venue_amd_costs_hotel";
	// Tagespauschale Hotel
	const VENUE_AMD_ALL_INCLUSIVE_COSTS = "venue_amd_all_inclusive_costs";
	
	
	// Standardorte und -veranstalter
	const VENUE_BERNRIED			= "venue_bernried";
	const PROVIDER_GENERALI			= "provider_generali";
	
	// zusätzliche Benutzerdaten
	// adp-nummer
	const USR_UDF_ADP_NUMBER			= "usr_udf_adp_number";  //deprecated
	const USR_UDF_ADP_GEV_NUMBER		= "usr_udf_adp_number";
	const USR_UDF_ADP_VFS_NUMBER		= "usr_udf_adp_vfs_number";


	// stellennummer/vermittlernummer
	const USR_UDF_JOB_NUMMER		= "usr_udf_job_number";
	// geburtsort
	const USR_UDF_BIRTHPLACE		= "usr_udf_birthplace";
	// geburtsname
	const USR_UDF_BIRTHNAME			= "usr_udf_birthname";
	// IHK-Registernummer
	const USR_UDF_IHK_NUMBER		= "usr_udf_ihk_number";
	// AD-Titel
	const USR_UDF_AD_TITLE			= "usr_udf_ad_title";
	// Vermittlerschlüssel
	const USR_UDF_AGENT_KEY			= "usr_udf_agent_key";
	

	//Stellungsschlüssel VFS
	const USR_UDF_AGENT_KEY_VFS		= "usr_udf_agent_key_vfs";
	//Stellung VFS	
	const USR_UDF_AGENT_POSITION_VFS= "usr_udf_agent_position_vfs";


	
	// Gesellschaftstitel
	const USR_UDF_COMPANY_TITLE		= "usr_udf_company_title"; //deprecated

	//Paisy-Personalnummer VFS
	const USR_UDF_PAISY_NUMBER 		= "usr_udf_paisy_number";
	//Kostenstelle VFS
	const USR_UDF_FINANCIAL_ACCOUNT	= "usr_udf_financial_account";
	


	
	// private Kontaktdaten, für geschäftliche Kontaktdaten werden
	// die Standard-ILIAS-Felder verwendet
	const USR_UDF_PRIV_EMAIL		= "usr_udf_priv_email";
	const USR_UDF_PRIV_STREET		= "usr_udf_priv_street";
	const USR_UDF_PRIV_CITY			= "usr_udf_priv_city";
	const USR_UDF_PRIV_ZIPCODE		= "usr_udf_priv_zipcode";
	
	const USR_UDF_PRIV_STATE		= "usr_udf_priv_state"; //deprecated
	const USR_UDF_PRIV_PHONE		= "usr_udf_priv_phone"; //mobile phone!
	const USR_UDF_PRIV_FAX			= "usr_udf_priv_fax"; //deprecated
	
	// Eintritts- und Austrittsdatum
	const USR_UDF_ENTRY_DATE		= "usr_udf_entry_date";
	const USR_UDF_EXIT_DATE			= "usr_udf_exit_date";
	
	// Status (????)
	const USR_UDF_STATUS			= "usr_udf_status"; //deprecated
	// HPE
	const USR_UDF_HPE				= "usr_udf_hpe"; //deprecated
	
	// WBD-Stuff
	// type of service for user
	const USR_TP_TYPE				= "usr_udf_tp_type";
	// Vermittlernummer bei der WBD
	const USR_BWV_ID				= "usr_udf_bwv_id";
	// how is the okz of the user determined
	const USR_WBD_OKZ				= "usr_udf_wbd_okz";
	
	// how is the "Vermittlungstätigkeit" determined
	/*
	global roles -> USR_WBD_STATUS
	"OD/LD/BD/VD/VTWL", "DBV/VL-EVG", "DBV-UVG" -> 1 - Angestellter Außendienst
	"AVL", "HA", "BA", "NA" -> 2 - Ausschließlichkeitsvermittler
	"VP" -> 3 - Makler
	*/

	const USR_WBD_STATUS			= "usr_udf_wbd_status";
	const USR_WBD_CERT_PERIOD_BEGIN = "usr_udf_wbd_cert_period_begin";
	const USR_WBD_DID_REGISTRATION	= "usr_udf_wbd_did_registration";
	const USR_WBD_COM_EMAIL			= "usr_udf_wbd_com_email";



	static $UDF_FIELD_ORDER = array(
		'Emailadresse (privat)'
		,'Geburtsname'
		,'Geburtsort'
		,'Straße (privat)'
		,'Postleitzahl (privat)'
		,'Ort (privat)'

		,'ADP-Nummer VFS'
		,'ADP-Nummer GEV'
		
		,'Vermittlernummer GEV'
		,'Vermittlerschlüssel GEV'

		,'Stellungsschlüssel VFS'
		,'Stellung VFS'
				
		,'Paisy-Personalnummer VFS'
		,'Kostenstelle VFS'
		
		,'AD-Titel'
		,'Eintrittsdatum'
		,'Austrittsdatum'
		,'IHK Registernummer'
		
		,'Hat WBD-Registrierung durchgeführt'
		,'TP-Typ'
		,'Zuweisung WBD OKZ'
		,'Zuweisung WBD Vermittlerstatus'
		,'BWV-ID'
		,'Beginn erste Zertifizierungsperiode'
		,'Email WBD'
		
	);


	// Role mapping
	static $VMS_ROLE_MAPPING = array(
		601 => array("DBV EVG",		"DBV"),
		602 => array("NA",			"Mitarbeiter"),
		603 => array("BA 84",		"Mitarbeiter"),
		604 => array("BA 84",		"Mitarbeiter"),
		606 => array("BA 84",		"Mitarbeiter"),
		607 => array("BA 84",		"Mitarbeiter"),
		608 => array("VP",			"Mitarbeiter"),
		609 => array("OD/BD",		"Vorgesetzter"),
		610 => array("HA 84",		"DBV"),
		613 => array("DBV UVG",		"DBV"),
		614 => array("DBV EVG",		"DBV"),
		615 => array("DBV EVG",		"DBV"),
		616 => array("DBV UVG",		"DBV"),
		617 => array("DBV EVG",		"DBV"),
		618 => array("DBV EVG",		"DBV"),
		620 => array("DBV EVG",		"DBV"),
		625 => array("OD/BD",		"Vorgesetzter"),
		628 => array("HA 84",		"Vorgesetzter"),
		630 => array("HA 84",		"Vorgesetzter"),
		632 => array("HA 84",		"Vorgesetzter"),
		633 => array("HA 84",		"Vorgesetzter"),
		634 => array("AVL",			"Vorgesetzter"),
		649 => array("OD/BD",		"Vorgesetzter"),
		650 => array("VP",			"Mitarbeiter"),
		651 => array("VP",			"Mitarbeiter"),
		653 => array("NA",			"Mitarbeiter"),
		655 => array("NA",			"Mitarbeiter"),
		657 => array("NA",			"Mitarbeiter"),
		661 => array("NA",			"Mitarbeiter"),
		664 => array("NA",			"Mitarbeiter"),
		671 => array("FD",			"Vorgesetzter"),
		674 => array("UA",			"Mitarbeiter"),
		675 => array("DBV EVG",		"DBV"),
		679 => array("VP",			"Mitarbeiter"),
		690 => array("HA 84",		"Vorgesetzter"),
		693 => array("NA",			"Mitarbeiter"),
		694 => array("BA 84",		"Mitarbeiter")
	);
	
	// Names of roles where we should be tolerant in the email at the
	// registration (#608)
	static $EMAIL_TOLERANCE_ROLES = array(
		  "DBV/VL-EVG"
		, "DBV-UVG"
		, "OD/LD/BD/VD/VTWL"
		);

	// Names of roles where users do not need to pay fees
	static $NO_PAYMENT_ROLES = array(
		  "Administrator"
		, "Administrator-eingeschränkt"
		, "Administrator-Voll"
		, "OD/LD/BD/VD/VTWL"
		, "LD/BD-Innen"
		, "DBV/VL-EVG"
		, "DBV-UVG"
		, "Azubi"
		, "ID FK"
		, "ID MA"
		, "int. Referent"
		, "ext. Referent"
		);
	
	// Names of roles where users need to pay the 
	static $NO_PREARRIVAL_PAYMENT_ROLES = array(
		  "Administrator"
		, "Admin-Voll"
		, "Admin-eingeschraenkt"
		, "Admin-Ansicht"
		, "OD/BD"
		, "FD"
		, "UA"
		, "HA 84"
		, "BA 84"
		, "Org PV 59"
		, "PV 59"
		, "Ausbildungsbeauftragter"
		, "ID FK"
		, "ID MA"
		, "OD/FD/BD ID"
		, "VA 59"
		, "VA HGB 84"
		, "NFK"
		, "FDA"
		, "Ausbilder"
		, "Azubi"
		, "Buchhaltung"
		, "Veranstalter"
		, "int. Trainer"
		, "ext. Trainer"
		, "OD-Betreuer"
		, "DBV UVG"
		, "DBV EVG"
		, "TP Service"
		, "TP Basis"
		, "VFS"
		);
	
	// Names of roles that count as admins
	static $ADMIN_ROLES = array(
		  "Administrator"
		, "Administrator-eingeschränkt"
		, "Administrator-Voll"
		);
	
	// Names of roles that count as superiors
	static $SUPERIOR_ROLES = array(
		  "il_orgu_superior_%"
		, "DBV"
		);

	// Names of roles that count as tutors
	static $TUTOR_ROLES = array(
		  "il_crs_tutor_%"
		);



	//OrgUnit Mappings (Personal OrgUnits)
	
	// for DBVen
	const DBV_POU_BASE_UNIT_KEY = "gev_dbv_pou_base_unit";
	const DBV_POU_TEMPLATE_UNIT_KEY = "gev_dbv_pou_template_unit_key";
	const CPOOL_UNIT_KEY = "gev_dbv_pou_cpool_unit_key";
	
	public function getDBVPOUBaseUnitId() {
		return $this->settings->get(self::DBV_POU_BASE_UNIT_KEY);
	}
	
	public function setDBVPOUBaseUnitId($a_val) {
		$this->settings->set(self::DBV_POU_BASE_UNIT_KEY, $a_val);
	}
	
	public function getDBVPOUTemplateUnitId() {
		return $this->settings->get(self::DBV_POU_TEMPLATE_UNIT_KEY);
	}
	
	public function setDBVPOUTemplateUnitId($a_val) {
		$this->settings->set(self::DBV_POU_TEMPLATE_UNIT_KEY, $a_val);
	}
	
	public function getCPoolUnitId() {
		return $this->settings->get(self::CPOOL_UNIT_KEY);
	}
	
	public function setCPoolUnitId($a_val) {
		$this->settings->set(self::CPOOL_UNIT_KEY, $a_val);
	}
	
	static $PERSONAL_ORGUNITS_MAPPING = array(
		'base' => 277, //ref 72
		'templates' => 281, //ref 74
		'cpool' => 285 //ref 76
	);
	
	

	// Role to "Status" mapping
	static $IDHGBAAD_STATUS_MAPPING = array(
		  "Administrator"			=> "ID"
		, "Admin-Voll"				=> "ID"
		, "Admin-eingeschraenkt"	=> "ID"
		, "Admin-Ansicht"			=> "ID"
		, "OD/BD"					=> "ID"
		, "FD"						=> "ID"
		, "UA"						=> "HGB §84"
		, "HA 84"					=> "HGB §84"
		, "BA 84"					=> "HGB §84"
		, "Org PV 59"				=> "AAD"
		, "PV 59"					=> "AAD"
		, "NA"						=> "HGB §84"
		, "VP"						=> "HGB §84"
		, "AVL"						=> "HGB §84"
		//, "Ausbildungsbeauftragter" => "nicht relevant"
		, "ID FK"					=> "ID"
		, "ID MA"					=> "ID"
		, "OD/FD/BD ID"				=> "ID"
		, "Agt-Id"					=> "HGB §84"
		, "VA 59"					=> "AAD"
		, "VA HGB 84"				=> "HGB §84"
		, "NFK"						=> "AAD"
		, "FDA"						=> "AAD"
		//, "Ausbilder"				=> "nicht relevant"
		, "Azubi"					=> "AAD"
		, "Buchhaltung"				=> "ID"
		//, "Veranstalter"			=> "nicht relevant"
		, "int. Trainer"			=> "ID"
		//, "ext. Trainer"			=> "nicht relevant"
		, "OD-Betreuer"				=> "ID"
		, "DBV UVG"					=> "AAD"
		, "DBV EVG"					=> "AAD"
		//, "TP Service"			=> "nicht relevant"
		//, "TP Basis"				=> "nicht relevant"
		//, "VFS"					=> "nicht relevant"
		);

	static $TEPTYPE_ORDER = array(
		'Training',
		
		'Projekt',
		'Veranstaltung / Tagung (Zentral)',
		'Trainer- / DBV Klausur (Zentral)',
		'Trainer Teammeeting',
		'Arbeitsgespräch',
		
		'AD Begleitung',
		'Firmenkunden',
		//'Aquise Pilotprojekt',
		'Akquise Pilotprojekt',
		'Individuelle Unterstützung SpV/FD',
		'Büro',
		
		'Urlaub beantragt',
		'Dezentraler Feiertag',
		'Urlaub genehmigt',
		'Ausgleichstag',
		'Krankheit',
		
		'OD-FD Meeting',
		'FD Gespräch',
		'RD-Gespräch',
		'AKL-Gespräch',
		'FD-MA Teammeeting',
		
		'Gewerbe-Arbeitskreis',
		'bAV-Arbeitskreis',
		'FDL-Arbeitskreis'
	);



	
	private function __construct() {
		$this->settings = new ilSetting(self::MODULE_NAME);
	}
	
	public function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevSettings();
		}
		
		return self::$instance;
	}
	
	public function get($a_field) {
		return $this->settings->get($a_field);
	}
	
	public function set($a_field, $a_value) {
		$this->settings->set($a_field, $a_value);
	}
	
	public function getAMDFieldId($a_field) {
		$field_id = explode(" ", $this->get($a_field));
		return $field_id[1];
	}
	
	public function getUDFFieldId($a_field) {
		return $this->get($a_field);
	}
	
	/*public function isAMDRecordUsed($a_record_id) {
			global $ilDB;
			$res = $ilDB->query("SELECT COUNT(*) AS cnt FROM settings ".
								"    WHERE module = 'gev' ".
								"      AND ".$ilDB->in("keyword", self::$amd_fields))
	}*/
}

?>
