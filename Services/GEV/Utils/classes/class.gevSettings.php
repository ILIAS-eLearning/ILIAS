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
	
	const YES = "Ja";
	const NO = "Nein";
	
	
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
	// Abgesagt
	const CRS_AMD_IS_CANCELLED		= "crs_amd_is_cancelled";
	
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
	// harte Stornofrist
	const CRS_AMD_ABSOLUTE_CANCEL_DEADLINE = "crs_amd_absolute_cancel_deadline";
	// relevante Themen
	const CRS_AMD_DBV_HOT_TOPIC = "crs_amd_dbv_hot_topic";
	// maximale Teilnehmer auf der Warteliste
	const CRS_AMD_MAX_WAITING_LIST_LENGTH = "crs_amd_max_waiting_list_length";
	
	// Anbieter
	const CRS_AMD_PROVIDER			= "crs_amd_provider";
	// Veranstaltungsort
	const CRS_AMD_VENUE				= "crs_amd_venue";
	const CRS_AMD_VENUE_FREE_TEXT	= "crs_amd_venue_free_text";
	// Übernachtungsort
	const CRS_AMD_ACCOMODATION		= "crs_amd_accomodation";
	// Veranstaltungsort Internet
	//const CRS_AMD_WEB_LOCATION		= "crs_amd_web_location";
	const CRS_AMD_WEBEX_LINK		= "crs_amd_webex_link";			// OLD ONLY FOR RENAME IN DATABASE USED
	const CRS_AMD_WEBEX_PASSWORD	= "crs_amd_webex_password";		// OLD ONLY FOR RENAME IN DATABASE USED
	const CRS_AMD_WEBEX_PASSWORD_TUTOR	= "crs_amd_webex_password_tutor";		// OLD ONLY FOR RENAME IN DATABASE USED
	const CRS_AMD_WEBEX_VC_CLASS_TYPE = "crs_amd_webex_vc_class_type"; // OLD ONLY FOR RENAME IN DATABASE USED
	const CRS_AMD_WEBEX_LOGIN_TUTOR = "crs_amd_webex_login_tutor"; // OLD ONLY FOR RENAME IN DATABASE USED

	const CRS_AMD_VC_LINK		= "crs_amd_vc_link";			// these are new general webinar links now
	const CRS_AMD_VC_PASSWORD	= "crs_amd_vc_password";		// these are new general webinar passwords now
	const CRS_AMD_VC_PASSWORD_TUTOR	= "crs_amd_vc_password_tutor";		// these are new general webinar tutor password
	const CRS_AMD_VC_CLASS_TYPE = "crs_amd_vc_class_type"; // new type of the virtual class
	const CRS_AMD_VC_LOGIN_TUTOR = "crs_amd_vc_login_tutor"; // new type of the virtual class

	const CRS_AMD_CSN_LINK			= "crs_amd_csn_link";	// this is not used anymore

	// Organisationseinheit TEP
	const CRS_AMD_TEP_ORGU			= "crs_amd_tep_orgu";

	//Traingsersteller
	const CRS_AMD_TRAINING_CREATOR 		= "crs_amd_training_creator";

	// Crs User PState
	const CRS_USR_STATE_SUCCESS			= "erfolgreich";
	const CRS_USR_STATE_SUCCESS_VAL		= "2";
	const CRS_USR_STATE_EXCUSED			= "entschuldigt";
	const CRS_USR_STATE_EXCUSED_VAL		= "3";
	const CRS_USR_STATE_NOT_EXCUSED		= "unentschuldigt";
	const CRS_USR_STATE_NOT_EXCUSED_VAL	= "4";

	//Highlight
	const CRS_AMD_HIGHLIGHT			="crs_amd_highlight";

	// Typen von Organisationseinheiten
	const ORG_TYPE_VENUE			= "org_unit_type_venue";
	const ORG_TYPE_PROVIDER			= "org_unit_type_provider";
	const ORG_TYPE_DEFAULT			= "org_unit_type_default";
	//Ref ID für OorgUnit Type BD
	const TYPE_ID_ORG_UNIT_TYPE_BD = "type_id_org_unit_type_bd";

	static $all_org_types = array( gevSettings::ORG_TYPE_VENUE
								 , gevSettings::ORG_TYPE_PROVIDER
								 , gevSettings::ORG_TYPE_DEFAULT
								 , gevSettings::TYPE_ID_ORG_UNIT_TYPE_BD
								 );

		static $dbv_hot_topics = array("3D Pflegevorsorge"
								 , "Rente Profil Plus"
								 , "bAV"
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
	
	
	// Firmenname
	const USR_UDF_COMPANY_NAME		= "usr_udf_company_name";
	
	
	// Gesellschaftstitel
	const USR_UDF_COMPANY_TITLE		= "usr_udf_company_title"; //deprecated

	//Paisy-Personalnummer VFS
	const USR_UDF_PAISY_NUMBER 		= "usr_udf_paisy_number";
	//Kostenstelle VFS
	const USR_UDF_FINANCIAL_ACCOUNT	= "usr_udf_financial_account";
	


	
	// private Kontaktdaten, für geschäftliche Kontaktdaten werden
	// die Standard-ILIAS-Felder verwendet
	const USR_UDF_PRIV_EMAIL		= "usr_udf_priv_email";		// NOT IN USE ANYMORE
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
	
	//basic templates for flexible decentral trainings
	const DCT_TPL_FLEX_PRESENCE = "dct_tpl_flex_presence";
	const DCT_TPL_FLEX_WEBINAR = "dct_tpl_flex_webinar";
	const DCT_TPL_MAIL_CSN = "dct_tpl_mail_csn";
	const DCT_TPL_MAIL_WEBEX = "dct_tpl_mail_webex";
	const DCT_TPL_MAIL_DECENTRAL_TRAINING = "dct_tpl_mail_decentral_training";

	//new course permissions
	const LOAD_SIGNATURE_LIST = "load_signature_list";
	const LOAD_MEMBER_LIST = "load_member_list";
	const VIEW_SCHEDULE_PDF = "view_schedule_pdf";
	const LOAD_CSN_LIST = "load_csn_list";
	const CHANGE_TRAINER = "change_trainer";
	const VIEW_MAILING = "view_mailing";
	const CANCEL_TRAINING = "cancel_training";

	//building block permissions
	const USE_BUILDING_BLOCK = "use_building_block";
	const EDIT_BUILDING_BLOCKS = "edit_building_blocks";

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
		
		,'Firmenname'
		
		,'Hat WBD-Registrierung durchgeführt'
		,'TP-Typ'
		,'Zuweisung WBD OKZ'
		,'Zuweisung WBD Vermittlerstatus'
		,'BWV-ID'
		,'Beginn erste Zertifizierungsperiode'
		,'Email WBD'
		,'Austrittsdatum WBD'
		,'Nächste durchzuführende WBD Aktion'
		,'Vorheriger TP-Service'
	);

	static $LOCAL_USER_MANDATORY_UDF_FIELDS = array(
		'Eintrittsdatum'
	);


	// Role mapping
	static $VMS_ROLE_MAPPING = array(
		610 => array("HA 84",		"DBV"),
		613 => array("DBV UVG",		"DBV"),
		614 => array("DBV EVG",		"DBV"),
		615 => array("DBV EVG",		"DBV"),
		616 => array("DBV UVG",		"DBV"),
		617 => array("DBV EVG",		"DBV"),
		618 => array("DBV EVG",		"DBV"),
		620 => array("DBV EVG",		"DBV"),
		675 => array("DBV EVG",		"DBV"),
		601 => array("DBV EVG",		"DBV"),
		
		603 => array("BA 84",		"Mitarbeiter"),
		604 => array("BA 84",		"Mitarbeiter"),
		606 => array("BA 84",		"Mitarbeiter"),
		607 => array("BA 84",		"Mitarbeiter"),
		
		608 => array("VP",			"Mitarbeiter"),
		650 => array("VP",			"Mitarbeiter"),
		651 => array("VP",			"Mitarbeiter"),
		679 => array("VP",			"Mitarbeiter"),

		602 => array("NA",			"Mitarbeiter"),
		653 => array("NA",			"Mitarbeiter"),
		655 => array("NA",			"Mitarbeiter"),
		657 => array("NA",			"Mitarbeiter"),
		661 => array("NA",			"Mitarbeiter"),
		664 => array("NA",			"Mitarbeiter"),
		693 => array("NA",			"Mitarbeiter"),
				
		694 => array("BA 84",		"Mitarbeiter"),
		
		634 => array("AVL",			"Mitarbeiter"),
		
		628 => array("HA 84",		"Mitarbeiter"),
		630 => array("HA 84",		"Mitarbeiter"),
		632 => array("HA 84",		"Mitarbeiter"),
		633 => array("HA 84",		"Mitarbeiter"),
		690 => array("HA 84",		"Mitarbeiter"),
		
		625 => array("OD/BD",		"Vorgesetzter"),
		609 => array("OD/BD",		"Vorgesetzter"),
		649 => array("OD/BD",		"Vorgesetzter"),
		671 => array("FD",			"Vorgesetzter"),
		674 => array("VP",			"Vorgesetzter"),
		9100 => array("ID FK",		"Vorgesetzter")
		
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
		, "Admin-Voll"
		, "Admin-eingeschraenkt"
		, "Admin-Ansicht"
		, "OD/BD"
		, "OD"
		, "BD"
		, "FD"
		, "Org PV 59"
		, "PV 59"
		, "Ausbildungsbeauftragter"
		, "ID FK"
		, "ID MA"
		, "OD/FD/BD ID"
		, "OD/FD ID"
		, "BD ID"
		, "VA 59"
		, "VA HGB 84"
		, "NFK"
		, "FDA"
		, "Ausbilder"
		, "Azubi"
		, "Veranstalter"
		, "int. Trainer"
		, "ext. Trainer"
		, "OD-Betreuer"
		, "DBV UVG"
		, "DBV EVG"
		);
	
	// Names of roles where users need to pay the 
	static $NO_PREARRIVAL_PAYMENT_ROLES = array(
		  "Administrator"
		, "Admin-Voll"
		, "Admin-eingeschraenkt"
		, "Admin-Ansicht"
		, "OD/BD"
		, "OD"
		, "BD"
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
		, "OD/FD ID"
		, "BD ID"
		, "VA 59"
		, "VA HGB 84"
		, "NFK"
		, "FDA"
		, "Ausbilder"
		, "Azubi"
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
		, "Admin-eingeschraenkt"
		, "Admin-Voll"
		);

	// Names of roles that count as system admins
	static $SYSTEM_ADMIN_ROLES = array(
		  "Administrator"
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

	// Names of roles that count as employees
	static $EMPLOYEE_ROLES = array(
		  "il_orgu_employee_%"
		);

	// Names of roles that count as crs manager
	static $CRS_MANAGER_ROLES = array(
		  "il_crs_admin_%"
		  ,"Pool Trainingsersteller"
		);

	// Will store the ref id of the orgu where the exited users should be put.
	const ORG_UNIT_EXITED = "org_unit_exited";
	
	public function getOrgUnitExited() {
		return $this->settings->get(self::ORG_UNIT_EXITED);
	}
	
	public function setOrgUnitExited($a_ref_id) {
		return $this->settings->set(self::ORG_UNIT_EXITED, $a_ref_id);
	}

	//Will store the ref id of the orgu where unassign user should be put
	const ORG_UNIT_UNASSIGNED_USER = "org_unit_unassigned_user";
	
	public function getOrgUnitUnassignedUser() {
		return $this->settings->get(self::ORG_UNIT_UNASSIGNED_USER);
	}

	public function setOrgUnitUnassignedUser($a_ref_id) {
		return $this->settings->set(self::ORG_UNIT_UNASSIGNED_USER,$a_ref_id);
	}

	//OrgUnit Mappings (Personal OrgUnits)
	
	// for DBVen, NA-Superiors and HAs
	const DBV_POU_BASE_UNIT_KEY = "gev_dbv_pou_base_unit";
	const DBV_POU_TEMPLATE_UNIT_KEY = "gev_dbv_pou_template_unit_key";
	const CPOOL_UNIT_KEY = "gev_dbv_pou_cpool_unit_key";
	const NA_POU_BASE_UNIT_KEY = "gev_na_pou_base_unit";
	const NA_POU_TEMPLATE_UNIT_KEY = "gev_na_pou_template_unit_key";
	const NA_POU_NO_ADVISER_UNIT_KEY = "gev_na_pou_no_adviser_unit_key";
	const HA_POU_BASE_UNIT_KEY = "gev_ha_pou_base_unit";
	const HA_POU_TEMPLATE_UNIT_KEY = "gev_ha_pou_template_unit";
	
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
	
	public function getNAPOUBaseUnitId() {
		return $this->settings->get(self::NA_POU_BASE_UNIT_KEY);
	}
	
	public function setNAPOUBaseUnitId($a_val) {
		$this->settings->set(self::NA_POU_BASE_UNIT_KEY, $a_val);
	}
	
	public function getNAPOUTemplateUnitId() {
		return $this->settings->get(self::NA_POU_TEMPLATE_UNIT_KEY);
	}
	
	public function setNAPOUTemplateUnitId($a_val) {
		$this->settings->set(self::NA_POU_TEMPLATE_UNIT_KEY, $a_val);
	}
	
	public function getNAPOUNoAdviserUnitId() {
		return $this->settings->get(self::NA_POU_NO_ADVISER_UNIT_KEY);
	}
	
	public function setNAPOUNoAdviserUnitId($a_val) {
		$this->settings->set(self::NA_POU_NO_ADVISER_UNIT_KEY, $a_val);
	}
	
	public function getHAPOUBaseUnitId() {
		return $this->settings->get(self::HA_POU_BASE_UNIT_KEY);
	}
	
	public function setHAPOUBaseUnitId($a_val) {
		$this->settings->set(self::HA_POU_BASE_UNIT_KEY, $a_val);
	}
	
	public function getHAPOUTemplateUnitId() {
		return $this->settings->get(self::HA_POU_TEMPLATE_UNIT_KEY);
	}
	
	public function setHAPOUTemplateUnitId($a_val) {
		$this->settings->set(self::HA_POU_TEMPLATE_UNIT_KEY, $a_val);
	}

	// Role to "Status" mapping
	static $IDHGBAAD_STATUS_MAPPING = array(
		  "Administrator"			=> "ID"
		, "Admin-Voll"				=> "ID"
		, "Admin-eingeschraenkt"	=> "ID"
		, "Admin-Ansicht"			=> "ID"
		, "OD/BD"					=> "AAD"
		, "OD"						=> "AAD"
		, "BD"						=> "AAD"
		, "FD"						=> "AAD"
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
		, "OD/FD ID"				=> "ID"
		, "BD ID"					=> "ID"
		, "Agt-Id"					=> "HGB §84"
		, "VA 59"					=> "AAD"
		, "VA HGB 84"				=> "HGB §84"
		, "NFK"						=> "AAD"
		, "FDA"						=> "AAD"
		//, "Ausbilder"				=> "nicht relevant"
		, "Azubi"					=> "AAD"
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
		'Weiterbildungstage',
		
		'AD-Begleitung',
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
		'FD-Gespräch',
		'OD-Gespräch',
		'AKL-Gespräch',
		'FD-MA Teammeeting',
		
		'Gewerbe-Arbeitskreis',
		'bAV-Arbeitskreis',
		'FDL-Arbeitskreis'
	);

	const AGENT_OFFER_USER_ID = "agent_offer_user_id";

	public function getAgentOfferUserId() {
		return $this->settings->get(self::AGENT_OFFER_USER_ID);
	}
	
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

	//basic templates for flexible decentral trainings	
	public function setDctTplFlexPresenceId($a_tpl_id) {
		$this->settings->set(self::DCT_TPL_FLEX_PRESENCE, $a_val);
	}

	public function getDctTplFlexPresenceId() {
		return $this->settings->get(self::DCT_TPL_FLEX_PRESENCE);
	}

	public function getDctTplFlexPresenceObjId() {
		$ref_id = $this->settings->get(self::DCT_TPL_FLEX_PRESENCE);
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		return gevObjectUtils::getObjId($ref_id);
	}

	public function setDctTplFlexWebinarId($a_tpl_id) {
		$this->settings->set(self::DCT_TPL_FLEX_WEBINAR, $a_val);
	}

	public function getDctTplFlexWebinarId() {
		return $this->settings->get(self::DCT_TPL_FLEX_WEBINAR);
	}

	public function getDctTplFlexWebinarObjId() {
		$ref_id = $this->settings->get(self::DCT_TPL_FLEX_WEBINAR);
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		return gevObjectUtils::getObjId($ref_id);
	}

	public function setTypeIDOrgUnitTypeDB($ref_id) {
		$this->settings->set(self::TYPE_ID_ORG_UNIT_TYPE_BD, $ref_id);
	}

	public function getTypeIDOrgUnitTypeDB() {
		return $this->settings->get(self::TYPE_ID_ORG_UNIT_TYPE_BD);
	}

	//id of duplicate user orgunit
	const DUPLICATE_USER_ORGUNIT_ID = "duplicate_user_orgunit_id";
	public function getDuplicatedUserOrgUnitId() {
		return $this->settings->get(self::DUPLICATE_USER_ORGUNIT_ID);
	}

	public function setDuplicatedUserOrgUnitId($obj_id) {
		$this->settings->set(self::DUPLICATE_USER_ORGUNIT_ID, $obj_id);
	}
}
