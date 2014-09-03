<#1>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$tselect = ilAdvancedMDFieldDefinition::TYPE_SELECT;
$ttext = ilAdvancedMDFieldDefinition::TYPE_TEXT;
$tdate = ilAdvancedMDFieldDefinition::TYPE_DATE;
$tdatetime = ilAdvancedMDFieldDefinition::TYPE_DATETIME;
$tinteger = ilAdvancedMDFieldDefinition::TYPE_INTEGER;
$tfloat = ilAdvancedMDFieldDefinition::TYPE_FLOAT;
$tlocation = ilAdvancedMDFieldDefinition::TYPE_LOCATION;
$tmultiselect = ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT;
$tvenue = ilAdvancedMDFieldDefinition::TYPE_VENUE_SELECT;
$tprovider = ilAdvancedMDFieldDefinition::TYPE_PROVIDER_SELECT;
$tlongtext = ilAdvancedMDFieldDefinition::TYPE_LONG_TEXT;
$tschedule = ilAdvancedMDFieldDefinition::TYPE_SCHEDULE;

$gev_set = gevSettings::getInstance();

$records = 
array( "Zeitraum"
	 	=> array(null,
	 	   array( "Startdatum" =>	
	 	   				array( gevSettings::CRS_AMD_START_DATE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tdate
	 	   					 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
	 	   					 )
	 	   		, "Enddatum" =>
	 	   				array( gevSettings::CRS_AMD_END_DATE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tdate
	 	   					 )
	 	   		, "Zeitplan" =>
	 	   				array( gevSettings::CRS_AMD_SCHEDULE
	 	   					 , null
	 	   					 , false
	 	   					 , null
	 	   					 , $tschedule
	 	   					 )
	 	   		))
	 , "Orte und Anbieter"
	 	=> array( null, 
	 	   array( "Anbieter" =>
	 	   				array( gevSettings::CRS_AMD_PROVIDER
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tprovider
	 	   					 )
	 	   		, "Veranstaltungsort" =>
	 	   				array( gevSettings::CRS_AMD_VENUE
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tvenue
	 	   					 )
	 	   		, "Übernachtungsort" =>
	 	   				array( gevSettings::CRS_AMD_ACCOMODATION
	 	   					 , null
	 	   					 , true
	 	   					 , null
	 	   					 , $tvenue
	 	   					 )
	 	   		))
	 , "Buchungsmodalitäten"
	 	=> array( "Fristen und Teilnehmerzahlen", 
	 	   array( "Mindestteilnehmerzahl" =>
	 	   				array( gevSettings::CRS_AMD_MIN_PARTICIPANTS
	 	   					 , null
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Warteliste"	=>
	 	   				array( gevSettings::CRS_AMD_WAITING_LIST_ACTIVE
	 	   					 , null
	 	   					 , false
	 	   					 , array( "Ja"
	 	   					 		, "Nein"
	 	   					 		)
	 	   					 , $tselect
	 	   					 )
	 	   		, "Maximalteilnehmerzahl" =>
	 	   					array( gevSettings::CRS_AMD_MAX_PARTICIPANTS
	 	   						 , null
	 	   						 , false
	 	   						 , array("min" => 0)
	 	   						 , $tinteger
	 	   						 )
	 	   		, "Stornofrist" =>
	 	   				array( gevSettings::CRS_AMD_CANCEL_DEADLINE
	 	   					 , "Tage vor dem Seminar, bis zu denen noch kostenfrei storniert werden kann."
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "Buchungsfrist" =>
	 	   				array( gevSettings::CRS_AMD_BOOKING_DEADLINE
	 	   					 , "Tage vor dem Seminar, bis zu denen das Seminar gebucht werden kann."
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
				 			 // if this is changed, gevUserUtils::getCourseHighlights
				 			 // needs to be changed as well!!
	 	   					 )
	 	   		, "Absage Wartelist" =>
	 	   				array( gevSettings::CRS_AMD_CANCEL_WAITING
	 	   					 , "Tag vor dem Seminar, an dem die Warteliste abgesagt wird."
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		))
	 , "Inhalte" 
		=> array( "Inhalte und Medien des Trainings",
		   array( "Trainingskategorie" =>
				 		array( gevSettings::CRS_AMD_TOPIC
				 			 , null
				 			 , true
				 			 , array( "Fachwissen"
				 			 		, "SUHK - Privatkunden"
				 			 		, "SUHK - Firmenkunden"
				 			 		, "Leben und Rente"
				 			 		, "Betriebliche Altersvorsorge"
				 			 		, "Kooperationspartner"
				 			 		, "Vertrieb"
				 			 		, "Akquise / Verkauf"
				 			 		, "Beratungs- und Tarifierungstools"
				 			 		, "Büromanagment"
				 			 		, "Neue Medien"
				 			 		, "Unternehmensführung"
				 			 		, "Agenturmanagment"
				 			 		, "Führung"
				 			 		, "Persönlichkeit"
				 			 		, "Erstausbildung"
				 			 		, "Ausbilder"
				 			 		, "Azubi"
				 			 		, "Qualifizierungsprogramme")
				 			 , $tmultiselect
				 			 )
				, "Trainingsinhalte" =>
						array( gevSettings::CRS_AMD_CONTENTS
							 , "Beschreibung der Trainingsinhalte"
							 , false
							 , null
							 , $tlongtext
							 )
				, "Ziele und Nutzen" =>
						array( gevSettings::CRS_AMD_GOALS
							 , "Beschreibung des Nutzens der Teilnehmer"
							 , false
							 , null
							 , $tlongtext 
							 )
				, "Methoden" =>
						array( gevSettings::CRS_AMD_METHODS
							 , "Beim Training eingesetzte Methoden"
							 , true
							 , array( "Vortrag"
							 		, "Gruppenarbeit"
							 		, "Partnerarbeit"
							 		, "Einzelarbeit"
							 		, "Diskussion"
							 		, "Brainstorming"
							 		, "Rollenspiele"
							 		)
							 , $tmultiselect
							 )
				, "Medien" =>
						array( gevSettings::CRS_AMD_MEDIA
							 , "Beim Training eingesetzte Medien"
							 , true
							 , array( "PowerPoint"
							 		, "Flipchart"
							 		, "Metakarten"
							 		, "myGenerali"
							 		, "Spezialsoftware"
							 		, "Arbeitsblatt / Handout"
							 		, "Film"
							 		, "Internet / Intranet"
							 		)
							 , $tmultiselect
							 )
				))
	 , "Zielgruppen"
		=> array( "Zielgruppen des Trainings",
		   array( "Zielgruppen" => 
		   				array( gevSettings::CRS_AMD_TARGET_GROUP
		   					 , "Zielgruppe des Trainings"
		   					 , true
		   					 , array( "AD-Auszubildende (EVG)"
		   					 		, "Ausbildungsverantwortliche in Agenturen (EVG), die über einen Ausbildereignungsschein verfügen"
		   					 		, "Agenturleiter und Ausbilder in Agenturen (EVG)"
		   					 		, "Angestellter Außendienst (freie Vertriebe, EVG)"
		   					 		, "selbstständiger Außendienst (EVG)"
		   					 		, "Innenvertrieb (EVG)"
		   					 		, "Innenvertrieb gemeinsam mit Agenturleiter (EVG)"
		   					 		, "selbstständiger Außendienst (EVG) ab Karrierestufe GA"
		   					 		, "selbstständiger Außendienst (EVG) ab Karrierestufe HA"
		   					 		, "selbstständiger Außendienst (EVG) ab Karrierestufe BGA"
		   					 		, "Verkaufsleiter (EVG)"
		   					 		, "Agenturverkaufsleiter (EVG)"
		   					 		)
		   					 , $tmultiselect
		   					 )
		   		, "Zielgruppenbeschreibung" =>
		   				array( gevSettings::CRS_AMD_TARGET_GROUP_DESC
		   					 , "Beschreibung der Zielgruppe des Trainings"
		   					 , false
		   					 , null
		   					 , $tlongtext
		   					 )
		   ))
	 , "Bewertung"
	 	=> array("Bewertung des Trainings für die WBD und den ASTD-Report",
	 	   array( "Weiterbildungspunkte" =>
	 	   				array( gevSettings::CRS_AMD_CREDIT_POINTS
	 	   					 , "An die WBD zu meldende Zahl von Bildungspunkten"
	 	   					 , false
	 	   					 , array("min" => 0)
	 	   					 , $tinteger
	 	   					 )
	 	   		, "GDV-Lerninhalt" =>
	 	   				array( gevSettings::CRS_AMD_GDV_TOPIC
	 	   					 , "An die WBD zu meldendes Thema des Seminars"
	 	   					 , false
	 	   					 , array( "Privat-Vorsorge-Lebens-/Rentenversicherung"
	 	   					 		, "Privat-Vorsorge-Kranken-/Pflegeversicherung"
	 	   					 		, "Firmenkunden-Sach-/Schadensversicherung"
	 	   					 		, "Spartenübergreifend"
	 	   					 		, "Firmenkunden-Vorsorge (bAV/Personenversicherung)"
	 	   					 		, "Beratungskompetenz"
	 	   					 		, "Privat-Sach-/Schadenversicherung"
	 	   					 		)
	 	   					 , $tselect
	 	   					 )
	 	   		, "Fachschulung" =>
	 	   				array( gevSettings::CRS_AMD_EXPERT_TRAINING
	 	   					 , "Ist das Training eine Fachschulung?"
	 	   					 , false
	 	   					 , array( "Ja"
	 	   					 		, "Nein"
	 	   					 		)
	 	   					 , $tselect
	 	   					 )
	 	   		))
	 , "Abrechnung"
	 	=> array( null,
	 	   array( "Teilnahmegebühr" =>
	 	   				array( gevSettings::CRS_AMD_FEE
	 	   					 , ""
	 	   					 , false
	 	   					 , array("min" => 0
	 	   					 		,"decimals" => 2)
	 	   					 , $tfloat
	 	   					 )
	 	   		))
	, "Verwaltung"
		=> 	array( "Einstellungen zur Verwaltung der Trainings", 
			array( "Trainingsnummer" => 
						array( gevSettings::CRS_AMD_CUSTOM_ID		# 0 to save in settings
							 , "Trainingsnummer oder Nummernkreis"  # 1 description
							 , true 								# 2 searchable
							 , null 								# 3 definition
							 , $ttext 								# 4 type
							 // if this is changed, the custom id logic in gevCourseUtils
							 // needs to be changed as well!!
							 )
				 , "Lernart" =>
				 		array( gevSettings::CRS_AMD_TYPE
				 			 , "Art des Trainings"
				 			 , true
				 			 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
				 			 , array( "Präsenztraining"
				 			 		, "Webinar"
				 			 		, "Selbstlernkurs"
				 			 		, "Spezialistenschulung Präsenztraining"
				 			 		, "Spezialistenschulung Webinar"
									, "POT-Termin"
									)
				 			 // if this is changed, gevUserUtils::getCourseHighlights
				 			 // needs to be changed as well!!
				 			 , $tselect
				 			 )
				 , "Vorlage" =>
				 		array( gevSettings::CRS_AMD_IS_TEMPLATE
				 			 , "Ist dieses Objekt ein Vorlagenobjekt?"
				 			 , false
				 			 , array ( "Ja"
				 			 		 , "Nein"
				 			 		 )
				 			 , $tselect
				 			 // if this is changed, gevUserUtils::getPotentiallyBookableCourses
				 			 // needs to be changed as well!!
				 			 )
				 , "Vorlagentitel" =>
				 		array( gevSettings::CRS_AMD_TEMPLATE_TITLE
				 			 , "Name der verwendeten Vorlage (nicht ändern)"
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "Referenz-Id der Vorlage" =>
				 		array( gevSettings::CRS_AMD_TEMPLATE_REF_ID
				 			 , "ILIAS-Referenz-Id der verwendeten Vorlage (nicht ändern)"
				 			 , false
				 			 , array("min" => 0)
				 			 , $tinteger
				 			 )
				 ))

	);

gevAMDUtils::createAMDRecords($records, array("crs"));
?>

<#2>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Verwaltung"
						, "Nummernkreis"
						, gevSettings::CRS_AMD_CUSTOM_ID_TEMPLATE
						, "Zu verwendender Nummernkreis für diese Vorlage"
						, false
						, array( "AD20000 Veranstaltungen zum Vertriebswegebudget"
							   , "AD30000 Azubiseminare"
							   , "AD40000 FFS"
							   , "AD50000 Grund- und IHK-Ausbildung"
							   , "AD55000 GEP BA (ehem. Postphase)"
							   , "AD60000 Weiterbildung"
							   , "AD65000 Generali Entwicklungsprogramme"
							   , "AD70000 Webinare"
							   , "ST10000 Spezialistenveranstaltungen (SpezialistenTrainings)"
							   , "SL10000 Selbstlernkurse"
							   )
						, ilAdvancedMDFieldDefinition::TYPE_SELECT
						);

?>

<#3>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Weblink"
						, gevSettings::CRS_AMD_WEBEX_LINK
						, "Link zum virtuellen Klassenraum"
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);

?>

<#4>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Zeitraum"
						, "geplant für"
						, gevSettings::CRS_AMD_SCHEDULED_FOR
						, ""
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);

?>

<#5>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

global $ilDB;

$ilDB->manipulate("UPDATE adv_mdf_definition SET title = 'Link WebEX' WHERE title = 'Weblink'");
$ilDB->manipulate("UPDATE settings SET keyword = 'crs_amd_webex_link' WHERE keyword = 'crs_amd_web_location'");

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Passwort WebEX"
						, gevSettings::CRS_AMD_WEBEX_PASSWORD
						, "Passwort zum virtuellen Klassenraum"
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Link CSN"
						, gevSettings::CRS_AMD_CSN_LINK
						, "Link zu CSN"
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);

?>

<#6>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Organisatorisches"
						, gevSettings::CRS_AMD_ORGA
						, ""
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_LONG_TEXT
						);
?>