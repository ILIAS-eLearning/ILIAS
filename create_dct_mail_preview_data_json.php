<?php
	require_once("./Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();

	require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
	require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
	require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
	require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
	

	if(isset($_GET["crs_ref_id"]) || isset($_GET["crs_template_id"])) {

		$id = isset($_GET["crs_ref_id"]) ? $_GET["crs_ref_id"] : null;

		if($id === null) {
			$id = isset($_GET["crs_template_id"]) ? $_GET["crs_template_id"] : null;
		}
		
		$data = getMailDataByCrsRefId($id);
		createJson($data);
	}

	if(isset($_GET["crs_request_id"])) {
		$data = getMailDataByCrsRequestId($_GET["crs_request_id"]);
		createJson($data);
	}

	function createJson($data) {
		echo json_encode($data);
	}

	function getMailDataByCrsRefId($crs_ref_id) {
		global $ilDB;

		$crs_obj_id = gevObjectUtils::getObjId($crs_ref_id);
		$crs_utils = gevCourseUtils::getInstance($crs_obj_id);

		$vc_link = $crs_utils->getVirtualClassLink();
		$vc_link_http = ($vc_link) ? $crs_utils->getVirtualClassLinkWithHTTP() : null;

		$venue_title = $crs_utils->getVenueTitle();
		$venue_title = ($venue_title) ? $venue_title : $crs_utils->getVenueFreeText();

		$data_base = array("TRAININGSTYP" 					=> $crs_utils->getType()
							,"TRAININGSTITEL" 				=> $crs_utils->getTitle()
							,"ID" 							=> $crs_utils->getCustomId()
							,"STARTDATUM" 					=> $crs_utils->getStartDate()
							,"ZEITPLAN" 					=> $crs_utils->getSchedule()[0]
							,"INHALT" 						=> $crs_utils->getContents()
							,"ZIELE UND NUTZEN" 			=> $crs_utils->getGoals()
							,"WP" 							=> $crs_utils->getCreditPoints()
							,"VO-NAME" 						=> $venue_title
							,"VO-STRAßE" 					=> $crs_utils->getVenueStreet()
							,"VO-HAUSNUMMER" 				=> $crs_utils->getVenueHouseNumber()
							,"VO-PLZ" 						=> $crs_utils->getVenueZipcode()
							,"VO-ORT" 						=> $crs_utils->getVenueCity()
							,"VO-TELEFON" 					=> $crs_utils->getVenuePhone()
							,"VO-INTERNET" 					=> $crs_utils->getVenueHomepage()
							,"ORGANISATORISCHES" 			=> $crs_utils->getOrgaInfo()
							,"ZIELGRUPPEN" 					=> $crs_utils->getTargetGroup()
							,"METHODEN" 					=> $crs_utils->getMethods()
							,"ALLE TRAINER" 				=> $crs_utils->getTrainers()
							,"WEBINAR-LINK" 				=> $vc_link_http
							,"WEBINAR-PASSWORT" 			=> $crs_utils->getVirtualClassPassword()
							,"TV-NAME" 						=> $crs_utils->getTrainingOfficerName()
							,"TV-TELEFON" 					=> $crs_utils->getTrainingOfficerPhone()
							,"TV-EMAIL" 					=> $crs_utils->getTrainingOfficerEmail()
							,"VC-TYPE" 						=> $crs_utils->getVirtualClassType()
							//NOT IN USE AT THE MOMENT
							,"SALUTATION" 					=> "Frau"
							,"FIRST_NAME" 					=> "Maria"
							,"LAST_NAME" 					=> "Musterfrau"
							,"LOGIN" 						=> "MMusterfrau"
							,"ILIAS_URL" 					=> ""
							,"CLIENT_NAME"					=> "Generali"
							,"MOBIL" 						=> "+49154836525487"
							,"OD" 							=> "OD des Teilnehmers"
							,"VERMITTLERNUMMER"				=> "1352145-654"
							,"ADP GEV"						=> "124623"
							,"ADP VFS" 						=> "1354132"
							,"TRAININGSUNTERTITEL" 			=> $crs_utils->getSubtitle()
							,"TRAININGSTHEMEN" 				=> implode(", ", $crs_utils->getTopics()) //array
							,"MEDIEN" 						=> $crs_utils->getMedia() //array;
							,"STARTZEIT" 					=> $crs_utils->getFormattedStartTime().":00"
							,"ENDDATUM" 					=> $crs_utils->getEndDate() //wandel 
							,"ENDZEIT" 						=> $crs_utils->getFormattedEndTime().":00"
							,"TRAININGSBETREUER-VORNAME" 	=> $crs_utils->getMainAdminFirstname()
							,"TRAININGSBETREUER-NACHNAME" 	=> $crs_utils->getMainAdminLastname()
							,"TRAININGSBETREUER-TELEFON"	=> $crs_utils->getMainAdminPhone()
							,"TRAININGSBETREUER-EMAIL"		=> $crs_utils->getMainAdminEmail()
							,"TRAININGSERSTELLER-VORNAME" 	=> $crs_utils->getMainTrainingCreatorFirstname()
							,"TRAININGSERSTELLER-NACHNAME" 	=> $crs_utils->getMainTrainingCreatorLastname()
							,"TRAININGSERSTELLER-TELEFON"	=> $crs_utils->getMainTrainingCreatorPhone()
							,"TRAININGSERSTELLER-EMAIL"		=> $crs_utils->getMainTrainingCreatorEmail()
							,"TRAINER-NAME" 				=> $crs_utils->getMainTrainerName()
							,"TRAINER-TELEFON" 				=> $crs_utils->getMainTrainerPhone()
							,"TRAINER-EMAIL" 				=> $crs_utils->getMainTrainerEmail()
							,"HOTEL-NAME" 					=> $crs_utils->getAccomodationTitle()
							,"HOTEL-STRAßE" 				=> $crs_utils->getAccomodationStreet()
							,"HOTEL-HAUSNUMMER" 			=> $crs_utils->getAccomodationHouseNumber()
							,"HOTEL-PLZ" 					=> $crs_utils->getAccomodationZipcode()
							,"HOTEL-ORT" 					=> $crs_utils->getAccomodationCity()
							,"HOTEL-TELEFON" 				=> $crs_utils->getAccomodationPhone()
							,"HOTEL-EMAIL" 					=> $crs_utils->getAccomodationEmail()
							,"BUCHENDER_VORNAME" 			=> "Peter"
							,"BUCHENDER_NACHNAME" 			=> "Bucher"
							,"EINSATZTAGE" 					=> ""
							,"UEBERNACHTUNGEN"				=> ""
							,"VORABENDANREISE" 				=> ""
							,"NACHTAGABREISE" 				=> ""
							,"LISTE" 						=> ""
						);

		if($data_base["STARTDATUM"] !== null) {
			$data_base["STARTDATUM"] = $data_base["STARTDATUM"]->get(IL_CAL_DATE);
			$date = explode("-",$data_base["STARTDATUM"]);
			$data_base["STARTDATUM"] = $date[2].".".$date[1].".".$date[0];
		}

		if($data_base["ENDDATUM"] !== null) {
			$data_base["ENDDATUM"] = $data_base["ENDDATUM"]->get(IL_CAL_DATE);
			$date = explode("-",$data_base["ENDDATUM"]);
			$data_base["ENDDATUM"] = $date[2].".".$date[1].".".$date[0];
		}

		if($data_base["ZIELGRUPPEN"] !== null) {
			$data_base["ZIELGRUPPEN"] = implode(", ",$data_base["ZIELGRUPPEN"]);
		}

		if($data_base["INHALT"] !== null) {
			$data_base["INHALT"] = str_replace("\n", ", ", $data_base["INHALT"]);
			$data_base["INHALT"] = rtrim($data_base["INHALT"],", ");

		}

		if($data_base["MEDIA"] !== null) {
			$data_base["MEDIA"] = implode(", ", $data_base["MEDIA"]);
		}

		if($data_base["METHODEN"] !== null) {
			$data_base["METHODEN"] = implode(", ", $data_base["MEDIA"]);
		}

		if($data_base["ZIELE UND NUTZEN"] !== null) {
			$data_base["ZIELE UND NUTZEN"] = str_replace("\n", ", ", $data_base["ZIELE UND NUTZEN"]);
			$data_base["ZIELE UND NUTZEN"] = rtrim($data_base["ZIELE UND NUTZEN"],", ");
		}

		if(isset($_GET["venue_id"])) {
			addVenueData($data_base,$_GET["venue_id"]);
		}

		if(isset($_GET["trainer_ids"])) {
			$data_base["ALLE TRAINER"] = explode("|",$_GET["trainer_ids"]);
		}

		getTrainerFullName($data_base);
		replaceLastNewLine($data_base);
		
		return $data_base;
	}

	function getMailDataByCrsRequestId($crs_request_id) {
		global $ilDB;

		$request_db = new gevDecentralTrainingCreationRequestDB();

		$request = $request_db->request($crs_request_id);
		$crs_utils = gevCourseUtils::getInstance($request->templateObjId());

		$start_time = split(" ",$request->settings()->start()->get(IL_CAL_DATETIME))[1];
		$start_time = substr($start_time,0,5);
		$end_time = split(" ",$request->settings()->end()->get(IL_CAL_DATETIME))[1];
		$end_time = substr($end_time,0,5);

		$targetGroup = implode(",",$request->settings()->targetGroup());

		$venue_title = "";
		$venue_id = $request->settings()->venueObjId();

		$data_base = array("TRAININGSTYP" 					=> $crs_utils->getType()
							,"TRAININGSTITEL" 				=> $request->settings()->title()
							,"ID" 							=> $crs_utils->getCustomId()
							,"STARTDATUM" 					=> $request->settings()->start()->get(IL_CAL_DATE)
							,"ZEITPLAN" 					=> $start_time."-".$end_time
							,"INHALT" 						=> gevCourseBuildingBlockUtils::content(null, $ilDB, $crs_request_id)
							,"ZIELE UND NUTZEN" 			=> gevCourseBuildingBlockUtils::targetAndBenefits(null, $ilDB, $crs_request_id)
							,"WP" 							=> gevCourseBuildingBlockUtils::wp(null, $ilDB, $crs_request_id)
							,"VO-NAME" 						=> ""
							,"VO-STRAßE" 					=> $crs_utils->getVenueStreet()
							,"VO-HAUSNUMMER" 				=> $crs_utils->getVenueHouseNumber()
							,"VO-PLZ" 						=> $crs_utils->getVenueZipcode()
							,"VO-ORT" 						=> $crs_utils->getVenueCity()
							,"VO-TELEFON" 					=> $crs_utils->getVenuePhone()
							,"VO-INTERNET" 					=> $crs_utils->getVenueHomepage()
							,"ORGANISATORISCHES" 			=> $request->settings()->orgaInfo()
							,"ZIELGRUPPEN" 					=> $targetGroup
							,"METHODEN" 					=> $crs_utils->getMethods()
							,"ALLE TRAINER" 				=> $request->trainerIds()
							,"WEBINAR-LINK" 				=> $request->settings()->webinarLink()
							,"WEBINAR-PASSWORT" 			=> $request->settings()->webinarPassword()
							,"TV-NAME" 						=> $crs_utils->getTrainingOfficerName()
							,"TV-TELEFON" 					=> $crs_utils->getTrainingOfficerPhone()
							,"TV-EMAIL" 					=> $crs_utils->getTrainingOfficerEmail()
							,"VC-TYPE" 						=> $request->settings()->vcType()
							//NOT IN USE AT THE MOMENT
							,"SALUTATION" 					=> "Frau"
							,"FIRST_NAME" 					=> "Maria"
							,"LAST_NAME" 					=> "Musterfrau"
							,"LOGIN" 						=> "MMusterfrau"
							,"ILIAS_URL" 					=> ""
							,"CLIENT_NAME"					=> "Generali"
							,"MOBIL" 						=> "+49154836525487"
							,"OD" 							=> "OD des Teilnehmers"
							,"VERMITTLERNUMMER"				=> "1352145-654"
							,"ADP GEV"						=> "124623"
							,"ADP VFS" 						=> "1354132"
							,"TRAININGSUNTERTITEL" 			=> $crs_utils->getSubtitle()
							,"TRAININGSTHEMEN" 				=> implode(", ", $crs_utils->getTopics()) //array
							,"MEDIEN" 						=> $crs_utils->getMedia() //array;
							,"STARTZEIT" 					=> $crs_utils->getFormattedStartTime().":00"
							,"ENDDATUM" 					=> $crs_utils->getEndDate() //wandel 
							,"ENDZEIT" 						=> $crs_utils->getFormattedEndTime().":00"
							,"TRAININGSBETREUER-VORNAME" 	=> $crs_utils->getMainAdminFirstname()
							,"TRAININGSBETREUER-NACHNAME" 	=> $crs_utils->getMainAdminLastname()
							,"TRAININGSBETREUER-TELEFON"	=> $crs_utils->getMainAdminPhone()
							,"TRAININGSBETREUER-EMAIL"		=> $crs_utils->getMainAdminEmail()
							,"TRAININGSERSTELLER-VORNAME" 	=> $crs_utils->getMainTrainingCreatorFirstname()
							,"TRAININGSERSTELLER-NACHNAME" 	=> $crs_utils->getMainTrainingCreatorLastname()
							,"TRAININGSERSTELLER-TELEFON"	=> $crs_utils->getMainTrainingCreatorPhone()
							,"TRAININGSERSTELLER-EMAIL"		=> $crs_utils->getMainTrainingCreatorEmail()
							,"TRAINER-NAME" 				=> $crs_utils->getMainTrainerName()
							,"TRAINER-TELEFON" 				=> $crs_utils->getMainTrainerPhone()
							,"TRAINER-EMAIL" 				=> $crs_utils->getMainTrainerEmail()
							,"HOTEL-NAME" 					=> $crs_utils->getAccomodationTitle()
							,"HOTEL-STRAßE" 				=> $crs_utils->getAccomodationStreet()
							,"HOTEL-HAUSNUMMER" 			=> $crs_utils->getAccomodationHouseNumber()
							,"HOTEL-PLZ" 					=> $crs_utils->getAccomodationZipcode()
							,"HOTEL-ORT" 					=> $crs_utils->getAccomodationCity()
							,"HOTEL-TELEFON" 				=> $crs_utils->getAccomodationPhone()
							,"HOTEL-EMAIL" 					=> $crs_utils->getAccomodationEmail()
							,"BUCHENDER_VORNAME" 			=> "Peter"
							,"BUCHENDER_NACHNAME" 			=> "Bucher"
							,"EINSATZTAGE" 					=> ""
							,"UEBERNACHTUNGEN"				=> ""
							,"VORABENDANREISE" 				=> ""
							,"NACHTAGABREISE" 				=> ""
							,"LISTE" 						=> ""
						);

		if($data_base["STARTDATUM"] !== null) {
			$date = explode("-",$data_base["STARTDATUM"]);
			$data_base["STARTDATUM"] = $date[2].".".$date[1].".".$date[0];
		}

		if($data_base["ENDDATUM"] !== null) {
			$data_base["ENDDATUM"] = $data_base["ENDDATUM"]->get(IL_CAL_DATE);
			$date = explode("-",$data_base["ENDDATUM"]);
			$data_base["ENDDATUM"] = $date[2].".".$date[1].".".$date[0];
		}

		$venue = $request->settings()->venueObjId();

		if($venue) {
			addVenueData($data_base, $venue);
		} else {
			$data_base["VO-NAME"] = $request->settings()->venueText();
		}

		if($data_base["ZIELGRUPPEN"] !== null && is_array($data_base["ZIELGRUPPEN"])) {
			$data_base["ZIELGRUPPEN"] = implode(", ",$data_base["ZIELGRUPPEN"]);
		}

		if($data_base["INHALT"] !== null) {
			$data_base["INHALT"] = implode(", ", $data_base["INHALT"]);
		}

		if($data_base["ZIELE UND NUTZEN"] !== null) {
			$data_base["ZIELE UND NUTZEN"] = implode(", ", $data_base["ZIELE UND NUTZEN"]);
		}

		if($data_base["MEDIA"] !== null) {
			$data_base["MEDIA"] = implode(", ", $data_base["MEDIA"]);
		}

		if($data_base["METHODEN"] !== null) {
			$data_base["METHODEN"] = implode(", ", $data_base["MEDIA"]);
		}

		if(isset($_GET["venue_id"])) {
			addVenueData($data_base,$_GET["venue_id"]);
		}

		getTrainerFullName($data_base);
		replaceLastNewLine($data_base);

		return $data_base;
	}

	function addVenueData(&$data_base, $venue_id) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			$ven = gevOrgUnitUtils::getInstance($venue_id);

			$data_base["VO-NAME"] = $ven->getLongTitle();
			$data_base["VO-STRAßE"] = $ven->getStreet();
			$data_base["VO-HAUSNUMMER"] = $ven->getHouseNumber();
			$data_base["VO-PLZ"] = $ven->getZipcode();
			$data_base["VO-ORT"] = $ven->getCity();
			$data_base["VO-TELEFON"] = $ven->getContactPhone();
			$data_base["VO-INTERNET"] = $ven->getHomepage();
	}

	function getTrainerFullName(&$data_base) {
		$ids = $data_base["ALLE TRAINER"];
		$names = array();

		foreach ($ids as $key => $value) {
			$usr_utils = gevUserUtils::getInstance($value);
			$names[] = $usr_utils->getFormattedContactInfo();
		}

		$data_base["ALLE TRAINER"] = implode("|",$names);
	}

	function replaceLastNewLine(&$data_base) {
		$data_base["ZIELE UND NUTZEN"] =  rtrim($data_base["ZIELE UND NUTZEN"],"\n");
	}