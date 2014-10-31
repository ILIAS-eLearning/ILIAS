<#1>
<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

$tselect = ilAdvancedMDFieldDefinition::TYPE_SELECT;
$ttext = ilAdvancedMDFieldDefinition::TYPE_TEXT;
$tdate = ilAdvancedMDFieldDefinition::TYPE_DATE;
$tdatetime = ilAdvancedMDFieldDefinition::TYPE_DATETIME;
$tinteger = ilAdvancedMDFieldDefinition::TYPE_INTEGER;
$tfloat = ilAdvancedMDFieldDefinition::TYPE_FLOAT;
$tlocation = ilAdvancedMDFieldDefinition::TYPE_LOCATION;
$tmultiselect = ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT;

$gev_set = gevSettings::getInstance();

$records_org = 
array( "Adresse"
		=> 	array( "Adressdaten der Organisationseinheit", 
			array( "Straße" => 
						array( gevSettings::ORG_AMD_STREET			# 0 to save in settings
							 , null									# 1 description
							 , true 								# 2 searchable
							 , null 								# 3 definition
							 , $ttext 								# 4 type
							 )
				 , "Hausnummer" =>
				 		array( gevSettings::ORG_AMD_HOUSE_NUMBER
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "Postleitzahl" =>
				 		array( gevSettings::ORG_AMD_ZIPCODE
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 , "Stadt" =>
				 		array( gevSettings::ORG_AMD_CITY
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				 ))
	 , "Kontaktdaten" 
		=> array( "Ansprechpartner in der Organisationseinheit",
		   array( "Kontaktperson" =>
				 		array( gevSettings::ORG_AMD_CONTACT_NAME
				 			 , null
				 			 , true
				 			 , null
				 			 , $ttext
				 			 )
				, "Telefon" =>
						array( gevSettings::ORG_AMD_CONTACT_PHONE
							 , null
							 , true
							 , null
							 , $ttext
							 )
				, "Fax" =>
						array( gevSettings::ORG_AMD_CONTACT_FAX
							 , null
							 , true
							 , null
							 , $ttext
							 )
				, "E-Mail" =>
						array( gevSettings::ORG_AMD_CONTACT_EMAIL
							 , null
							 , true
							 , null
							 , $ttext
							 )
				, "Homepage" =>
						array( gevSettings::ORG_AMD_HOMEPAGE
							 , null
							 , true
							 , null
							 , $ttext
							 )
				))
	);

$records_venue = 
array("Ort"
	 	=> array(null,
	 	   array( "Ort" =>
	 	   				array( gevSettings::VENUE_AMD_LOCATION
	 	   					 , null
	 	   					 , false
	 	   					 , null
	 	   					 , $tlocation
	 	   					 )
	 	   		))
	 , "Preise"
		=> array( "Übernachtungs- und Verpflegungspreise",
		   array( "Kosten je Übernachtung" => 
		   				array( gevSettings::VENUE_AMD_COSTS_PER_ACCOM
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Pauschale Frühstück" => 
		   				array( gevSettings::VENUE_AMD_COSTS_BREAKFAST
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Pauschale Mittagessen" => 
		   				array( gevSettings::VENUE_AMD_COSTS_LUNCH
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Nachmittagspauschale" => 
		   				array( gevSettings::VENUE_AMD_COSTS_COFFEE
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Pauschale Abendessen" => 
		   				array( gevSettings::VENUE_AMD_COSTS_DINNER
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
				, "Pauschale Tagesverpflegung" => 
		   				array( gevSettings::VENUE_AMD_COSTS_FOOD
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
		   		, "Vollkostenpauschale Hotel" =>
		   				array( gevSettings::VENUE_AMD_COSTS_HOTEL
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
		   		, "Tagespauschale Hotel" =>
		   				array( gevSettings::VENUE_AMD_ALL_INCLUSIVE_COSTS
		   					 , null
		   					 , true
		   					 , array( "min" => 0
		   					 		, "decimals" => 2
		   					 		)
		   					 , $tfloat
		   					 )
		   ))
	);

$records_org_ids = gevAMDUtils::createAMDRecords($records_org, array(array("orgu", "orgu_type")));
$records_venue_ids = gevAMDUtils::createAMDRecords($records_venue, array(array("orgu", "orgu_type")));

require_once("Customizing/global/plugins/Modules/OrgUnit/OrgUnitTypeHook/GEVOrgTypes/classes/class.ilGEVOrgTypesPlugin.php");

// This is hacky!
ilGEVOrgTypesPlugin::$allow = true;

gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_VENUE, $records_org_ids);
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_VENUE, $records_venue_ids);
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_PROVIDER, $records_org_ids);
gevOrgUnitUtils::assignAMDRecordsToOrgUnitType(gevSettings::ORG_TYPE_DEFAULT, $records_org_ids);

ilGEVOrgTypesPlugin::$allow = false;
?>