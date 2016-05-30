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
				, "Bildungsprogramm" =>
						array( gevSettings::CRS_AMD_EDU_PROGRAMM
							 , null
							 , true
							 , array( "zentrales Training"
									, "dezentrales Training"
									, "Grundausbildung"
									, "Azubi-Ausbildung"
							 	    )
							 , $tselect
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
		   					 , array( 
		   					 		"AD-Auszubildende (EVG)"
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
	 			, "Mice-ID" =>
	 					array( gevSettings::CRS_AMD_MICE_ID
	 						 , ""
	 						 , false
	 						 , null
	 						 , $ttext
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
				 			 		, "Virtuelles Training"
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
						, "Link WebEx"
						, gevSettings::CRS_AMD_VC_LINK
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

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Passwort WebEX"
						, gevSettings::CRS_AMD_VC_PASSWORD
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

<#7>
<?php

global $ilDB;

$ilDB->manipulate("UPDATE adv_mdf_definition SET title = 'Webinar Link' WHERE title = 'Link WebEX'");
$ilDB->manipulate("UPDATE adv_mdf_definition SET title = 'Webinar Passwort' WHERE title = 'Passwort WebEX'");

?>

<#8>
<?php
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");

gevAMDUtils::removeAMDField(gevSettings::CRS_AMD_CSN_LINK);

?>

<#9>
<?php
$def = serialize(
	array( "AD20000 Veranstaltungen zum Vertriebswegebudget"
		   , "AD30000 Azubiseminare"
		   , "AD40000 FFS"
		   , "AD50000 Grund- und IHK-Ausbildung"
		   , "AD55000 GEP BA (ehem. Postphase)"
		   , "AD60000 Weiterbildung"
		   , "AD65000 Generali Entwicklungsprogramme"
		   , "AD70000 Webinare"
		   , "ST10000 Dezentrales Training"
		   , "SL10000 Selbstlernkurse"
		   , "AD80000 Weiterbildung / Bildungskatalog für Führungskräfte"
	)
);

global $ilDB;
$ilDB->manipulate("UPDATE adv_mdf_definition SET field_values = '$def' WHERE title = 'Nummernkreis'");
?>

<#10>
<?php
//Trainingstyp
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
$amdutils = gevAMDUtils::getInstance();
$amdutils->updateTitleOfAMDField(gevSettings::CRS_AMD_TYPE, 'Trainingstyp', 'Typ des Trainings');

?>


<#11>
<?php
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
$options = array(
	"Partnerverkäufer § 59"
	,"§ 84"
	,"Organisierende Partnerverkäufer"
	,"Leiter einer Filialdirektion" 
	,"Nebenberufsagenten"
	,"Vertriebsassistenten"
	,"DBV UVG"
	,"DBV EVG"
	,"Inhaber einer Unternehmeragentur" 
	,"Innenvertrieb FD / OD / BD"
	,"Innenvertrieb HV"
	,"Organisationsdirektor"
	,"Nachwuchsführungskräfte" 
	,"Unabhängige Vertriebspartner"
	,"Trainer"
	,"Auszubildende"
	,"Ausbildungsverantwortliche in den Agenturen mit ADA-Schein"
	,"Innenvertrieb Agenturen"

);

$amdutils = gevAMDUtils::getInstance();
$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_TARGET_GROUP, $options);

?>

<#12>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "für Organisationseinheit"
						, gevSettings::CRS_AMD_TEP_ORGU
						, ""
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEP_ORGU_SELECT
						);

?>

<#13>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Buchungsmodalitäten"
						, "harte Stornofrist"
						, gevSettings::CRS_AMD_ABSOLUTE_CANCEL_DEADLINE
						, "Tage vor dem Seminar, bis zu denen noch storniert werden kann."
						, false
						, array("min" => 0)
						, ilAdvancedMDFieldDefinition::TYPE_INTEGER
						);

?>

<#14>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Webinar Passwort Trainer"
						, gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
						, "Passwort zum virtuellen Klassenraum für den Trainer"
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);
?>

<#15>
<?php
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
//Reihenfolge im array ist gleich der Reihenfolge der neuen Position
$gev_settings = array(gevSettings::CRS_AMD_PROVIDER
					 ,gevSettings::CRS_AMD_VENUE
					 ,gevSettings::CRS_AMD_ACCOMODATION
					 ,gevSettings::CRS_AMD_VC_LINK
					 ,gevSettings::CRS_AMD_VC_PASSWORD
					 ,gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
					 ,gevSettings::CRS_AMD_ORGA
					 ,gevSettings::CRS_AMD_TEP_ORGU);

$amdutils = gevAMDUtils::getInstance();
$amdutils->updatePositionOrderAMDField($gev_settings);
?>

<#16>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$amdutils = gevAMDUtils::getInstance();

$amdutils->addAMDField( "Orte und Anbieter"
						, "VC-Typ"
						, gevSettings::CRS_AMD_VC_CLASS_TYPE
						, ""
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_SELECT
						);

$options = array("AT&T Connect");
$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_VC_CLASS_TYPE, $options);

$gev_settings = array(gevSettings::CRS_AMD_PROVIDER
					 ,gevSettings::CRS_AMD_VENUE
					 ,gevSettings::CRS_AMD_ACCOMODATION
					 ,gevSettings::CRS_AMD_VC_CLASS_TYPE
					 ,gevSettings::CRS_AMD_VC_LINK
					 ,gevSettings::CRS_AMD_VC_PASSWORD
					 ,gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
					 ,gevSettings::CRS_AMD_ORGA
					 ,gevSettings::CRS_AMD_TEP_ORGU);

$amdutils->updatePositionOrderAMDField($gev_settings);
?>

<#17>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$amdutils = gevAMDUtils::getInstance();

$amdutils->addAMDField( "Orte und Anbieter"
						, "Webinar Login Trainer"
						, gevSettings::CRS_AMD_VC_LOGIN_TUTOR
						, "Login zum virtuellen Klassenraum für den Trainer"
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);

$gev_settings = array(gevSettings::CRS_AMD_PROVIDER
					 ,gevSettings::CRS_AMD_VENUE
					 ,gevSettings::CRS_AMD_ACCOMODATION
					 ,gevSettings::CRS_AMD_VC_CLASS_TYPE
					 ,gevSettings::CRS_AMD_VC_LINK
					 ,gevSettings::CRS_AMD_VC_PASSWORD
					 ,gevSettings::CRS_AMD_VC_LOGIN_TUTOR
					 ,gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
					 ,gevSettings::CRS_AMD_ORGA
					 ,gevSettings::CRS_AMD_TEP_ORGU);

$amdutils->updatePositionOrderAMDField($gev_settings);
?>

<#18>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$amdutils = gevAMDUtils::getInstance();

$amdutils->addAMDField( "Inhalte"
						, "Relevante Themen"
						, gevSettings::CRS_AMD_DBV_HOT_TOPIC
						, "Auswahl aktuell relevanter Themen"
						, true
						, null
						, ilAdvancedMDFieldDefinition::TYPE_SELECT
						);

$gev_settings = array(gevSettings::CRS_AMD_PROVIDER
					 ,gevSettings::CRS_AMD_VENUE
					 ,gevSettings::CRS_AMD_ACCOMODATION
					 ,gevSettings::CRS_AMD_VC_CLASS_TYPE
					 ,gevSettings::CRS_AMD_VC_LINK
					 ,gevSettings::CRS_AMD_VC_PASSWORD
					 ,gevSettings::CRS_AMD_VC_LOGIN_TUTOR
					 ,gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
					 ,gevSettings::CRS_AMD_ORGA
					 ,gevSettings::CRS_AMD_TEP_ORGU
					 ,gevSettings::CRS_AMD_DBV_HOT_TOPIC);

$options = array(
	"Rente Profil Plus",
	"bAV",
	"3D Pflegevorsorge");

$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_DBV_HOT_TOPIC, $options);
$amdutils->updatePositionOrderAMDField($gev_settings);
?>

<#19>
<?php
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");

gevAMDUtils::addAMDField( "Orte und Anbieter"
						, "Freitext Veranstaltungsort"
						, gevSettings::CRS_AMD_VENUE_FREE_TEXT
						, ""
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);


//Reihenfolge im array ist gleich der Reihenfolge der neuen Position
$gev_settings = array(gevSettings::CRS_AMD_PROVIDER
					 ,gevSettings::CRS_AMD_VENUE
					 ,gevSettings::CRS_AMD_VENUE_FREE_TEXT
					 ,gevSettings::CRS_AMD_ACCOMODATION
					 ,gevSettings::CRS_AMD_VC_LINK
					 ,gevSettings::CRS_AMD_VC_PASSWORD
					 ,gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
					 ,gevSettings::CRS_AMD_ORGA
					 ,gevSettings::CRS_AMD_TEP_ORGU);

$amdutils = gevAMDUtils::getInstance();
$amdutils->updatePositionOrderAMDField($gev_settings);
?>

<#20>
<?php
	require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
	require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$amdutils = gevAMDUtils::getInstance();
	$options = array("AT&T Connect", "CSN", "Webex");
	$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_VC_CLASS_TYPE, $options);
?>

<#21>
<?php
	require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
	require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevSettings.php");
	$gev_settings = array(gevSettings::CRS_AMD_PROVIDER
					 ,gevSettings::CRS_AMD_VENUE
					 ,gevSettings::CRS_AMD_ACCOMODATION
					 ,gevSettings::CRS_AMD_VC_CLASS_TYPE
					 ,gevSettings::CRS_AMD_VC_LINK
					 ,gevSettings::CRS_AMD_VC_PASSWORD
					 ,gevSettings::CRS_AMD_VC_LOGIN_TUTOR
					 ,gevSettings::CRS_AMD_VC_PASSWORD_TUTOR
					 ,gevSettings::CRS_AMD_ORGA
					 ,gevSettings::CRS_AMD_TEP_ORGU
					 ,gevSettings::CRS_AMD_DBV_HOT_TOPIC);
	
	$amdutils = gevAMDUtils::getInstance();
	$amdutils->updatePositionOrderAMDField($gev_settings);
	$options = array("AT&T Connect", "CSN", "Webex");
	$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_VC_CLASS_TYPE, $options);
?>

<#22>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$records = 
array( "Highlight"
		=> array(null,
				array( "Highlight" =>
						array( gevSettings::CRS_AMD_HIGHLIGHT
							 , null
							 , false
							 , array("Ja")
							 , ilAdvancedMDFieldDefinition::TYPE_SELECT
							 )
				)
			)
	);

gevAMDUtils::createAMDRecords($records, array("crs"));
?>

<#23>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Buchungsmodalitäten"
						, "Länge Warteliste"
						, gevSettings::CRS_AMD_MAX_WAITING_LIST_LENGTH
						, "Anzahl der Plätze auf der Warteliste."
						, false
						, array("min" => 0)
						, ilAdvancedMDFieldDefinition::TYPE_INTEGER
						);

$gev_settings = array(gevSettings::CRS_AMD_MIN_PARTICIPANTS
					 ,gevSettings::CRS_AMD_MAX_PARTICIPANTS
					 ,gevSettings::CRS_AMD_BOOKING_DEADLINE
					 ,gevSettings::CRS_AMD_CANCEL_DEADLINE
					 ,gevSettings::CRS_AMD_ABSOLUTE_CANCEL_DEADLINE
					 ,gevSettings::CRS_AMD_WAITING_LIST_ACTIVE
					 ,gevSettings::CRS_AMD_MAX_WAITING_LIST_LENGTH
					 ,gevSettings::CRS_AMD_CANCEL_WAITING);
	
	$amdutils = gevAMDUtils::getInstance();
	$amdutils->updatePositionOrderAMDField($gev_settings);
?>

<#24>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$amdutils = gevAMDUtils::getInstance();

$options = array
	( "Fachwissen"
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
	, "Grundausbildung"
	, "Ausbilder"
	, "Erstausbildung"
	, "Qualifizierungsprogramme"
	, "Assistanceleistungen"
	, "Investment"
	, "Kranken und Pflege"
	, "Rechtsschutz"
	, "Bausparen und Finanzieren"
	);

$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_TOPIC, $options);
?>

<#25>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Verwaltung"
						, "Abgesagt"
						, gevSettings::CRS_AMD_IS_CANCELLED
						, "Dieser Kurs wurde abgesagt."
						, false
						, array("Nein","Ja")
						, ilAdvancedMDFieldDefinition::TYPE_SELECT
						);
?>

<#26>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

gevAMDUtils::addAMDField( "Verwaltung"
						, "Trainingsersteller"
						, gevSettings::CRS_AMD_TRAINING_CREATOR
						, "Login des Trainingserstellers."
						, false
						, null
						, ilAdvancedMDFieldDefinition::TYPE_TEXT
						);
?>

<#27>
<?php
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

$amdutils = gevAMDUtils::getInstance();

$options = array(
	  "SUHK - Privatkunden"
	, "SUHK - Firmenkunden"
	, "Leben und Rente"
	, "Betriebliche Altersvorsorge"
	, "Akquise / Verkauf"
	, "Beratungs- und Tarifierungstools"
	, "Büromanagement"
	, "Neue Medien"
	, "Unternehmensführung"
	, "Agenturmanagement"
	, "Führung"
	, "Grundausbildung"
	, "Erstausbildung"
	, "Qualifizierungsprogramme"
	, "Assistanceleistungen"
	, "Investment"
	, "Kranken und Pflege"
	, "Rechtsschutz"
	, "Bausparen und Finanzieren"
	);

$amdutils->updateOptionsOfAMDField(gevSettings::CRS_AMD_TOPIC, $options);
?>