<?php

/**
* Calendar - edit entry
* update an existing Appointment and/or create a new one
*
* @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
* @author MArtin Schumacher <ilias@auchich.de>
* @author Mark Ulbrich <Mark_Ulbrich@web.de>
*
* @package ilias
*/

require_once "./include/inc.header.php";
require_once "./classes/Calendar/class.ilAppointment.php";
require_once "./classes/Calendar/class.ilCalendarTools.php";
require_once "./classes/Calendar/class.ilAppointmentHandler.php";
require_once "./classes/Calendar/class.ilCalDBHandler.php";
require_once "./classes/Calendar/class.ilCalGroupHandler.php";

$confirmation = FALSE;
$delete = FALSE;
$update = FALSE;
$updateconf = FALSE;
$day_warning = FALSE;
$deleteConfirmation = FALSE;
$appointment = new ilAppointment();
$cal = new ilCalendarTools();
$users = null;

$today = getdate();
$todayts = mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]);

if($_GET["ts"] != null) {
	$chosents = $_GET["ts"];
}

$objekt = new ilCalGroupHandler;

if ($_POST["day"] != null) {
	$appointment = new ilAppointment();
	$appointment->setOwnerId($ilias->account->getId());
	$appointment->setCategoryId($_POST["category"]);
	$appointment->setDescription($_POST["description"]);
	$appointment->setPriorityId($_POST["priority"]);
	$appointment->setAccess($_POST["access"]);
	
	if ($_POST["groups"] != null && count($_POST["groups"])>0) {
		if ($_POST["groups"][0] == 0) {
			$users = $objekt->getAllUsers();
		}
		else {
			for ($i=0;$i<count($_POST["groups"]);$i++) {
				$users = array_merge($users, $objekt->getUserIDs($_POST["groups"][$i]));
			}
		}
		$users=array_values(array_unique($users));
	}
	else {
		$users=array($ilias->account->getId());
	}
	
	if	($_POST["aid"] != null) {
		$update = TRUE;
		$appointment->setAppointmentId($_POST["aid"]);
	}

	if ($_POST["term"] != null) {
		$appointment->setTerm($_POST["term"]);
	}
	else {
		$appointmentErrTerm = TRUE;
		$appointmentErr = TRUE;
	}
		$appointment->setLocation($_POST["location"]);
	if (checkdate($_POST["month"], $_POST["day"], $_POST["year"]) == TRUE) {
		$appointmentErrTime = FALSE;
		if ($_POST["hour"] != null) {
			if ($_POST["hour"] >= 0 && $_POST["hour"] <= 23 && $cal->isNumeric($_POST["hour"])) {
				$hour = $_POST["hour"];
			}
			else {
				$appointmentErr = TRUE;
				$appointmentErrTime = TRUE;
			}
		}
		else {
			$hour = 0;
		}
		if ($_POST["minute"] != null) {
			if ($_POST["minute"] >= 0 && $_POST["minute"] <= 59 && $cal->isNumeric($_POST["minute"])){
				$minute = $_POST["minute"];
			}
			else {
				$appointmentErr = TRUE;
				$appointmentErrTime = TRUE;
			}
		}
		else {
			$minute = 0;
		}
		if ($appointmentErrTime == FALSE) {
		    $appointment->setStartTimestamp(mktime($hour, $minute, 0, $_POST["month"], $_POST["day"], $_POST["year"]));
		}
		if($_POST["day"]>28)
			$day_warning = TRUE;
		else
			$day_warning = FALSE;
	}
	else {
		$appointmentErrDate = TRUE;
		$appointmentErr = TRUE;
	}
	
	if ($cal->isNumeric($_POST["duration_d"]) &&
		 $cal->isNumeric($_POST["duration_h"]) && 
		 $cal->isNumeric($_POST["duration_m"])) {
		$duration = 0;
		if ($_POST["duration_d"] != null && $_POST["duration_d"] != "") {
			$duration = ($_POST["duration_d"]*1440);
		}
		if ($_POST["duration_h"] != null && $_POST["duration_h"] != "") {
			$duration = $duration + ($_POST["duration_h"]*60);
		}
		if ($_POST["duration_m"] != null && $_POST["duration_m"] != "") {
		   $duration = $duration + $_POST["duration_m"];
		}
		$appointment->setDuration($duration);
	}
	else {
		$appointmentErr = True;
		$appointmentErrDur = True;
	}
	
	if ($_POST["rpt"] == "y") {
		$appointment->setSerial(1);
		$appointment->setSer_type($_POST["ser_type"]);
		if ($_POST["ser_type"] == "ser_week") {
			if ($_POST["rpt_sun"] == "y") {
				$ser_day = "y";
			}
			else {
				$ser_day = "n";
			}
			if ($_POST["rpt_mon"] == "y") {
				$ser_day = $ser_day . "y";
			}
			else {
				$ser_day = $ser_day . "n";
			}
			if ($_POST["rpt_tue"] == "y") {
				$ser_day = $ser_day . "y";
			}
			else {
				$ser_day = $ser_day . "n";
			}
			if ($_POST["rpt_wed"] == "y") {
				$ser_day = $ser_day . "y";
			}
			else {
				$ser_day = $ser_day . "n";
			}
			if ($_POST["rpt_thu"] == "y") {
				$ser_day = $ser_day . "y";
			}
			else {
				$ser_day = $ser_day . "n";
			}
			if ($_POST["rpt_fri"] == "y") {
				$ser_day = $ser_day . "y";
			}
			else {
				$ser_day = $ser_day . "n";
			}
			if ($_POST["rpt_sat"] == "y") {
				$ser_day = $ser_day . "y";
			}
			else {
				$ser_day = $ser_day . "n";
			}
			$appointment->setSer_days($ser_day);
		}
	}
	else {
		$appointment->setSerial(0);
	}
	
	if ($_POST["rpt_stop_d"] != null or $_POST["rpt_stop_m"] != null or $_POST["rpt_stop_y"] != null) {
	        if (checkdate($_POST["rpt_stop_m"], $_POST["rpt_stop_d"], $_POST["rpt_stop_y"]) == TRUE &&
					$cal->isNumeric($_POST["rpt_stop_m"]) && 
	        		$cal->isNumeric($_POST["rpt_stop_d"]) && 
	        		$cal->isNumeric($_POST["rpt_stop_y"])) {
	        			
	        		$ser_stop = mktime(23, 59, 0, $_POST["rpt_stop_m"], $_POST["rpt_stop_d"], $_POST["rpt_stop_y"]);
	                $appointment->setSer_stop($ser_stop);
	        }
	        else {
	                $appointmentErr = TRUE;
	                $appointmentErrStp = TRUE;
	        }
	}
	else {
		$appointment->setSer_stop(0);
	}
	if ($appointmentErr != True) {
		$appointmentHandler = new ilAppointmentHandler();
		$startTimestamp = $appointment->getStartTimestamp();
		$endTimestamp = strtotime( "+".$appointment->getDuration()." minutes", $startTimestamp);
		$result = $appointmentHandler->getAppointmentArrayList($ilias->account->getId(), $startTimestamp, $endTimestamp);
	}
	else {
		if ($appointmentErrDate == True) {
			$appointment->setStartTimestamp($todayts);
		}
	}
	
	if (count($result) != 0) {
		$app_double = TRUE;
	}

	if ($appointmentErr == TRUE) {

		$errString ="";

		if ($appointmentErrTerm == TRUE) {
		    $errString = $errString . "Kurzbeschreibung fehlt <br>";
		}
		if ($appointmentErrTime == TRUE) {
		    $errString = $errString . "Zeitangabe fehlerhaft <br>";
		}
		if ($appointmentErrDate == TRUE) {
		    $errString = $errString . "Datum fehlerhaft <br>";
		}
		if ($appointmentErrDur == TRUE) {
		    $errString = $errString . "Zeitraum fehlerhaft <br>";
		}
		if ($appointmentErrStp == TRUE) {
		    $errString = $errString . "Enddatum fehlerhaft <br>";
		}
		if ($appointmentErrDouble == TRUE) {
			$app_double = TRUE;
		}
		if ($_POST["term"] != null && $appointmentErrTerm != TRUE) {
		    $appointment->setTerm($_POST["term"]);
		}
		if ($_POST["description"] != null) {
	   		$appointment->setDescription($_POST["description"]);
		}
		if ($_POST["location"] != null) {
	        $appointment->setLocation($_POST["location"]);
		}
		if ($_POST["hour"] != null && $appointmentErrTime != TRUE) {
	        $hour = $_POST["hour"];
		}
		if ($_POST["minute"] != null && $appointmentErrTime != TRUE) {
	        $minute = $_POST["minute"];
		}
		if ($_POST["duration_d"] != null) {
	        $dur_day = $_POST["duration_d"];
		}
		if ($_POST["duration_h"] != null) {
	        $dur_hour = $_POST["duration_h"];
		}
		if ($_POST["duration_m"] != null) {
	        $dur_minute = $_POST["duration_m"];
		}
		$appointment->setCategoryId($_POST["category"]);
		$appointment->setPriorityId($_POST["priority"]);
		$appointment->setAccess($_POST["access"]);
		$appointment->setSer_type($_POST["ser_type"]);
		if ($_POST["rpt"] == "y") {
			$rpt = "y";
		}
		if ($_POST["rpt_sun"] == "y") {
			$rpt_sun = "y";
		}
		if ($_POST["rpt_mon"] == "y") {
			$rpt_mon = "y";
		}
		if ($_POST["rpt_tue"] == "y") {
			$rpt_tue = "y";
		}
		if ($_POST["rpt_wed"] == "y") {
			$rpt_wed = "y";
		}
		if ($_POST["rpt_thu"] == "y") {
			$rpt_thu = "y";
		}
		if ($_POST["rpt_fri"] == "y") {
			$rpt_fri = "y";
		}
		if ($_POST["rpt_sat"] == "y") {
			$rpt_sat = "y";
		}
		if ($_POST["rpt_stop_d"] != null && $appointmentErrStp != TRUE) {
   	     $rpt_stop_d = $_POST["rpt_stop_d"];
		}
		if ($_POST["rpt_stop_m"] != null && $appointmentErrStp != TRUE) {
   	     $rpt_stop_m = $_POST["rpt_stop_m"];
		}
		if ($_POST["rpt_stop_y"] != null && $appointmentErrStp != TRUE) {
   	     $rpt_stop_y = $_POST["rpt_stop_y"];
		}
   	$appointmentErr = FALSE;
   	$appointmentErrTerm = FALSE;
   	$appointmentErrTime = FALSE;
    	$appointmentErrDate = FALSE;
   	$appointmentErrStp = FALSE;
   }
	else {
		if ($update != TRUE) {
			$appointmentHandler->insertAppointment($users, $appointment);
			$confirmation = TRUE;
		}
		else {
			$aehhhWieNennIchDasJetzt = $appointmentHandler->getSingleAppointment($appointment->getAppointmentId());
			$appointment->setAppointmentUnionId($aehhhWieNennIchDasJetzt->getAppointmentUnionId());
			$updateOK = $appointmentHandler->appointmentUpdate($ilias->account->getId(), $appointment);
			
			$updateconf = TRUE;
		}
	}
}
elseif ($_GET["aid"] != null) {
	$appointmentHandler = new ilAppointmentHandler();
	$appointmentId = $_GET["aid"];
	$appointment = $appointmentHandler->getSingleAppointment($appointmentId);
	if ($_GET["ts"] != null) {
		$appointment->setStartTimestamp($_GET["ts"]);
	}
	$update = TRUE;
	$confirmation = FALSE;
	$delete = FALSE;
	$edit = TRUE;
}
elseif ($_GET["delete"] != null) {
	$userId = $ilias->account->getId();
	$appointmentId = $_GET["delete"];
	$rnts = $_GET["ts"];
	$appointmentHandler = new ilAppointmentHandler();
	$appointment = $appointmentHandler->getSingleAppointment($appointmentId);
	//$tempArray = getdate($temp);
	//$appTSArray = getdate($appointment->getStartTimestamp());
	//$rnts = mktime ($appTSArray["hour"], $appTSArray["minute"], $appTSArray["seconds"], $tempArray["mon"], $tempArray["mday"], $tempArray["year"]);
	
	if($appointment->getSerial() == 1) {
		$deleteOK = $appointmentHandler->appointmentRepeatsNot($appointmentId, $rnts);
		$appointment->setStartTimestamp($rnts);
		$delete = TRUE;
		$confirmation = FALSE;
		$edit = FALSE;
		$update = FALSE;
	}
	else {
		$appointmentUnionId = $appointment->getAppointmentUnionId();
		$deleteOK = $appointmentHandler->deleteAppointment($userId, $appointmentId, $appointmentUnionId);
		$delete = TRUE;
		$confirmation = FALSE;
		$edit = FALSE;
		$update = FALSE;
	}
}
elseif ($_GET["deleteS"] != null) {
	$userId = $ilias->account->getId();
	$appointmentId = $_GET["deleteS"];
	$rnts = $_GET["ts"];
	$appointmentHandler = new ilAppointmentHandler();
	$appointment = $appointmentHandler->getSingleAppointment($appointmentId);
	$appointmentUnionId = $appointment->getAppointmentUnionId();
	//echo "appointmentUnionId: ".$appointmentUnionId."<br>";
	$deleteOK = $appointmentHandler->deleteAppointment($userId, $appointmentId, $appointmentUnionId);
	$delete = TRUE;
	$confirmation = FALSE;
	$edit = FALSE;
	$update = FALSE;
}
elseif ($_GET["ts"] != null) {
	
	$appointment = new ilAppointment();
	$appointment->setStartTimestamp($_GET["ts"]);
}

if ($confirmation == FALSE && $delete != TRUE && $updateconf != TRUE) {
	$tpl->addBlockFile("CONTENT", "content", "tpl.cal_edit_entry.html");
	if (isset($_GET["ts"])) {
		$chosents = $_GET["ts"];
	}
	//add template for buttons
	$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

	// display tabs
	include "./include/inc.calendar_tabs.php";


	if ($edit == TRUE) 
	{
		//add template for buttons
		if($appointment->getSerial() == 1) {	
			$tpl->setCurrentBlock("btn_cell");
			$tpl->setVariable("BTN_LINK","cal_edit_entry.php?delete=".$appointment->getAppointmentId()."&ts=".$appointment->getStartTimestamp());
			$tpl->setVariable("BTN_TXT","l&ouml;sche Einzeltermin");
			$tpl->parseCurrentBlock();
			
			$tpl->setCurrentBlock("btn_cell");
			$tpl->setVariable("BTN_LINK","cal_edit_entry.php?deleteS=".$appointment->getAppointmentId()."&ts=".$appointment->getStartTimestamp());
			$tpl->setVariable("BTN_TXT","l&ouml;sche Terminserie");
			$tpl->parseCurrentBlock();
		}
		else {
			$tpl->setCurrentBlock("btn_cell");
			$tpl->setVariable("BTN_LINK","cal_edit_entry.php?delete=".$appointment->getAppointmentId()."&ts=".$appointment->getStartTimestamp());
			$tpl->setVariable("BTN_TXT","l&ouml;sche Termin");
			$tpl->parseCurrentBlock();
		}

	}

	$tpl->touchBlock("btn_row");

	$tpl->setCurrentBlock("content");
	$tpl->setVariable("VAL_aid", $appointment->getAppointmentId());
	$tpl->setVariable("TXT_PAGEHEADLINE", "Termin hinzuf&uuml;gen / &auml;ndern");
	$tpl->setVariable("TXT_Error", $errString);
	$tpl->setVariable("TXT_APPOINTMENT", $lng->txt("appointment"));
	$tpl->setVariable("TXT_Term", "Kurzbeschreibung");
	$tpl->setVariable("VAL_Term", $appointment->getTerm());
	$tpl->setVariable("TXT_Description", "Beschreibung");
	$tpl->setVariable("VAL_Description", $appointment->getDescription());
	$tpl->setVariable("TXT_Location", "Ort");
	$tpl->setVariable("VAL_Location", $appointment->getLocation());
	$tpl->setVariable("TXT_Date", "Datum");
	$count_txt = "";
	$counter = 1;
	$appDate = getDate($appointment->getStartTimestamp());
	$selDay = $appDate["mday"];
	if ($_POST["day"] != null || $_POST["day"] != "")
		$selDay = $_POST["day"];
	while ($counter <= 31) {
		if ($selDay == $counter) {
			$checked = "selected";
		}
        $count_txt = $count_txt . "<OPTION VALUE=\"{$counter}\" {$checked}>".$cal->addLeadingZero($counter)."</OPTION>";
        $checked = "";
        $counter = $counter + 1;
	}
	   $tpl->setVariable("VAL_day_count", $count_txt);
	$count_txt = "";
	$counter = 1;
	$selMonth = $appDate["mon"];
	if ($_POST["month"] != null || $_POST["month"] != "")
		$selMonth = $_POST["month"];
	while ($counter <= 12) {
		if ($selMonth == $counter) {
			$checked = "selected";
		}
		$count_txt = $count_txt . "<OPTION VALUE=\"{$counter}\" {$checked}>".$cal->addLeadingZero($counter)."</OPTION>";
		$checked = "";
		$counter = $counter + 1;
	}
	$tpl->setVariable("VAL_month_count", $count_txt);
	$tpl->setVariable("VAR_Year_act", date("Y"));
	$selYear = $appDate["year"];
	if ($_POST["year"] != null || $_POST["year"] != "")
		$selYear = $_POST["year"];

	if ($selYear == date("Y"))
		$tpl->setVariable("VAL_Year_act_sel", "selected");	
		
	$tpl->setVariable("VAR_Year_nxt", date("Y", strtotime("+1 year")));
	if ($selYear == date("Y", strtotime("+1 year")))
		$tpl->setVariable("VAL_Year_nxt_sel", "selected");	
		
	$tpl->setVariable("TXT_Time", "Uhrzeit");
	
	$selHour = $appDate["hours"];
	if ($_POST["hour"] != null || $_POST["hour"] != "")
		$selHour = $_POST["hour"];
		
	$tpl->setVariable("VAL_hour", $selHour);
	
	$selMinute = $appDate["minutes"];
	if ($_POST["minute"] != null || $_POST["minute"] != "")
		$selMinute = $_POST["minute"];
		
	$tpl->setVariable("VAL_Minute", $selMinute);
	$tpl->setVariable("TXT_Time_expl", "z.B. 23:59");
	
	if (($_POST["duration_d"] == null || $_POST["duration_d"] == "") &&
		 ($_POST["duration_h"] == null || $_POST["duration_h"] == "") &&
		 ($_POST["duration_m"] == null || $_POST["duration_m"] == "")) {
		$d = $appointment->getDuration();
		$d_days = (int) (($d - ($d%1440)) / 1440);
		$d_hours = (int) (($d%1440) / 60);
		$d_minutes = (int) (($d%1440)%60);
	}
	else {
		$d_days = $_POST["duration_d"];
		$d_hours = $_POST["duration_h"];
		$d_minutes = $_POST["duration_m"];
	}
	$tpl->setVariable("TXT_Duration", "Dauer");
	$tpl->setVariable("TXT_Days", "Tag(e)");
	$tpl->setVariable("VAL_dur_d", $d_days);
	$tpl->setVariable("TXT_Hours", "Stunden");
	$tpl->setVariable("VAL_dur_h", $d_hours);
	$tpl->setVariable("TXT_Minutes", "Minuten");
	$tpl->setVariable("VAL_dur_m", $d_minutes);
	$tpl->setVariable("TXT_Priority", "Priorit&auml;t");
	$tpl->setVariable("TXT_Access", "Zugriff");
	$tpl->setVariable("TXT_Category", "Kategorie");
	$tpl->setVariable("TXT_Public", "&Ouml;ffentlich");
	$tpl->setVariable("TXT_Confidential", "Privat");
	$tpl->setVariable("TXT_Single", "Einzeltermin");
	$tpl->setVariable("TXT_Group", "Gruppentermin");
	$tpl->setVariable("TXT_Groups", "Gruppe");
			
	$groupy = $objekt->getGroups($ilias->account->getId());
	if(count($groupy) > 0) {
		foreach ($groupy as $value) {
			$groupyId = $value["ID"];
			$groupyTerm = $value["term"];
			if ($_POST["groups"] == $groupyId) {
				$checked = "selected";
			}
			$groupsBuffer = $groupsBuffer . "<OPTION VALUE=".$groupyId.">".$groupyTerm."</OPTION>";
		}
		$tpl->setVariable("VAL_Groups", $groupsBuffer);
		$groupsBuffer = "";
	}

	$tpl->setVariable("TXT_Serial_des", "Serientermin?");
	$tpl->setVariable("TXT_Ser_Type", "Art des Serientermins");
	$tpl->setVariable("VAR_Week", "Woche");
	$tpl->setVariable("VAR_Month", "Monat");
	$tpl->setVariable("VAR_Six_month", "Halbjahr");
	$tpl->setVariable("VAR_Year", "Jahr");
	$tpl->setVariable("TXT_Serial_days", "Tage der Wiederholung:");
	$tpl->setVariable("TXT_Sunday", "Sonntag");
	$tpl->setVariable("TXT_Monday", "Montag");
	$tpl->setVariable("TXT_Tuesday", "Dienstag");
	$tpl->setVariable("TXT_Wednesday", "Mittwoch");
	$tpl->setVariable("TXT_Thursday", "Donnerstag");
	$tpl->setVariable("TXT_Friday", "Freitag");
	$tpl->setVariable("TXT_Saturday", "Samstag");
	$tpl->setVariable("TXT_Stop_Serial", "Terminserie bis:");
	$tpl->setVariable("TXT_Serial", "Format: 31.12.1900");
	$tpl->setVariable("TXT_Submit", "Speichern");
	$tpl->setVariable("TXT_Cancel", "Zurücksetzen");

	if ($appointment->getAccess() == "Public") {
		$tpl->setVariable("VAL_public", "selected");
	}
	if ($appointment->getAccess() == "Confidential") {
		$tpl->setVariable("VAL_conf", "selected");
	}

	$dbhandler = new ilCalDBHandler();
	$resultP = $dbhandler->select("cal_priority", "", "", "priorityId");
	if($resultP->numRows() > 0)
	{
		while($rowP = $resultP->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($rowP["priorityId"] == $appointment->getPriorityId()) {
				$pri_chkd = "selected";
			}
			$pri_str = $pri_str . "<OPTION VALUE=\"" . $rowP["priorityId"] . "\" {$pri_chkd}> " . $rowP["term"] . "</OPTION>";
			$pri_chkd = "";
		}
	}

	$tpl->setVariable("VAL_priority_count", $pri_str);
	$resultC = $dbhandler->select("cal_category", "", "", "categoryid");
	if($resultC->numRows() > 0) {
		while($rowC = $resultC->fetchRow(DB_FETCHMODE_ASSOC)) {
			if ($rowC["categoryId"] == $cat_id) {
				$cat_chkd = "selected";
			}
			$cat_str = $cat_str . "<OPTION VALUE=\"" . $rowC["categoryId"] . "\" {$cat_chkd}> " . $rowC["term"] . "</OPTION>";
			$cat_chkd = "";
		}
	}
	$tpl->setVariable("VAL_category_count", $cat_str);

	if ($appointment->getSer_type() == "ser_week") {
		$tpl->setVariable("VAL_week", "selected");
	}
	if ($appointment->getSer_type() == "ser_month") {
		$tpl->setVariable("VAL_month", "selected");
	}
	if ($appointment->getSer_type() == "ser_halfayear") {
		$tpl->setVariable("VAL_halfayear", "selected");
	}
	if ($appointment->getSer_type() == "ser_year") {
		$tpl->setVariable("VAL_year", "selected");
	}
	if ($appointment->getSerial() == TRUE) {
		$tpl->setVariable("VAL_chkd", "checked");
	}
	if ($appointment->getSer_days() != null) {
		if (substr($appointment->getSer_days(), 0, 1) == "y") {
			$tpl->setVariable("VAL_sun_chkd", "checked");
		}
		if (substr($appointment->getSer_days(), 1, 1) == "y") {
			$tpl->setVariable("VAL_mon_chkd", "checked");
		}
		if (substr($appointment->getSer_days(), 2, 1) == "y") {
			$tpl->setVariable("VAL_tue_chkd", "checked");
		}
		if (substr($appointment->getSer_days(), 3, 1) == "y") {
			$tpl->setVariable("VAL_wed_chkd", "checked");
		}
		if (substr($appointment->getSer_days(), 4, 1) == "y") {
			$tpl->setVariable("VAL_thu_chkd", "checked");
		}
		if (substr($appointment->getSer_days(), 5, 1) == "y") {
			$tpl->setVariable("VAL_fri_chkd", "checked");
		}
		if (substr($appointment->getSer_days(), 6, 1) == "y") {
			$tpl->setVariable("VAL_sat_chkd", "checked");
		}
	}
	
	if ($appointment->getSer_stop() != null && $appointment->getSer_stop() != 0 && $appointment->getSer_stop() != "") {
		$tpl->setVariable("VAL_rpt_stop_d", date("d", $appointment->getSer_stop()));
		$tpl->setVariable("VAL_rpt_stop_m", date("m", $appointment->getSer_stop()));
		$tpl->setVariable("VAL_rpt_stop_y", date("Y", $appointment->getSer_stop()));
	}
	elseif(($_POST["rpt_stop_d"] != null || $_POST["rpt_stop_d"] != "") ||
			 ($_POST["rpt_stop_h"] != null || $_POST["rpt_stop_h"] != "") ||
			 ($_POST["rpt_stop_m"] != null || $_POST["rpt_stop_m"] != "")) {
		$tpl->setVariable("VAL_rpt_stop_d", $_POST["rpt_stop_d"]);
		$tpl->setVariable("VAL_rpt_stop_m", $_POST["rpt_stop_m"]);
		$tpl->setVariable("VAL_rpt_stop_y", $_POST["rpt_stop_y"]);
	}
}
elseif ($confirmation == TRUE || $delete == TRUE || $app_double == TRUE || $updateconf == TRUE) {

	$tpl->addBlockFile("CONTENT", "content", "tpl.cal_confirmation.html");
	if (isset($_GET["ts"])) {
		$chosents = $_GET["ts"];
	}
	//add template for buttons
	$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

	// display tabs
	include "./include/inc.calendar_tabs.php";

	$tpl->touchBlock("btn_row");
	$tpl->setCurrentBlock("content");
	$tpl->setVariable("TXT_PAGEHEADLINE","Bestätigung");
	$chosen = getDate($appointment->getStartTimestamp());
	if ($confirmation == TRUE) {
		$tpl->setVariable("TXT_TITLE", "Folgender Termin wurde gespeichert");
		if ($app_double == TRUE) {
			$txt = "TERMIN DOPPELT BELEGT";
		}
		if ($day_warning == TRUE) {
			if($app_double == TRUE)
				$txt = $txt."<br>";
			$txt = $txt."Es kann vorkommen, dass ein Monat weniger als ".$_POST["day"]." Tage hat.<br>".
			            "In diesen Fällen wird der Termin auf den letzen Tag im Monat gelegt.";
		}
		$tpl->setVariable("TXT_double", $txt);
		$appointmentshow = $cal->getMappedWeekday($chosen["wday"]).", ".$chosen["mday"].".".$cal->getMonth($chosen["mon"])." ".$chosen["year"] ." ". $appointment->getTerm();
		$tpl->setVariable("TXT_Confirmation", $appointmentshow);
	}
	elseif ($delete == TRUE) {
		if($deleteOK == false) {
			$tpl->setVariable("TXT_TITLE", "Folgender Termin wurde NICHT gelöscht");
			$appointmentshow = "Sie sind nicht berechtigt diesen Termin zu löschen.";
			$tpl->setVariable("TXT_Confirmation", $appointmentshow);
		}
		else {
			$tpl->setVariable("TXT_TITLE", "Folgender Termin wurde gelöscht");
			$appointmentshow = $cal->getMappedWeekday($chosen["wday"]).", ".$chosen["mday"].".".$cal->getMonth($chosen["mon"])." ".$chosen["year"]." ". $appointment->getTerm();
			$tpl->setVariable("TXT_Confirmation", $appointmentshow);
		}
	}
	elseif ($updateconf == TRUE) {
		if ($updateOK == false) {
			$tpl->setVariable("TXT_TITLE", "Der Termin wurde NICHT geändert:");
			$appointmentshow = "Sie sind nicht berechtigt den Termin zu ändern.";
			$tpl->setVariable("TXT_Confirmation", $appointmentshow);
		}
		else {
			$tpl->setVariable("TXT_TITLE", "Der Termin wurde folgendermaßen geändert:");
			$appointmentshow = $cal->getMappedWeekday($chosen["wday"]).", ".$chosen["mday"].".".$cal->getMonth($chosen["mon"])." ".$chosen["year"] ." ". $appointment->getTerm();
			$tpl->setVariable("TXT_Confirmation", $appointmentshow);
		}
	}
}
else {
	//echo "<font size=\"300%\" color=\"red\">ILIAS ist komplett abgestürzt.</font><br> Bitte melden Sie sich beim Administrator, damit er ILIAS von der Festplatte entfernt.<br>";
}
$tpl->show();
?>
