<?php
/**
* Calendar - showing the anonymized appointments od other users
*
* @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
* @author MArtin Schumacher <ilias@auchich.de>
* @author Mark Ulbrich <Mark_Ulbrich@web.de>
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./classes/Calendar/class.ilCalendarTools.php";
require_once "./classes/Calendar/class.ilAppointmentHandler.php";
require_once "./classes/Calendar/class.ilAppointment.php";
require_once "./classes/Calendar/class.ilCalGroupHandler.php";
			
if ($_GET["date"] != "" && $_GET["month"] != "" && $_GET["year"] != "" && $_GET["hour"] != "" && $_GET["minute"] != "") {
	$startPeriodTS = mktime($_GET["hour"],$_GET["minute"],0,$_GET["month"],$_GET["date"],$_GET["year"]);
	$startPeriodArray = getdate($startPeriodTS);
}
if ($_GET["durd"] != "" && $_GET["durh"] != "" && $_GET["durm"] != "") {
	$endPeriodTS = $startPeriodTS + ( (((($_GET["durd"] * 24) + $_GET["durh"]) * 60) + $_GET["durm"]) * 60 );
	$endPeriodArray = getdate($endPeriodTS);
}

if ($startPeriodTS > 0 && $endPeriodTS > 0) {

	$gh = new ilCalGroupHandler();
	$ah = new ilAppointmentHandler();
	$cal = new ilCalendarTools();
	$groupsTemp = explode ("_", $_GET["groups"]);
	$users = null;
	
	$allUsers = false;
	for ($i=0;$i<count($groupsTemp)-1;$i++) {
		if($groupsTemp[$i] == 0) {
			$allUsers = true;
		}
	}
		
	$users = null;
	if ($allUsers) {
		$users = $gh->getAllUsers();
	}
	else {
		for ($i=0;$i<count($groupsTemp);$i++) {
			if ($groupsTemp[$i] != "" && $groupsTemp[$i] != null) {
				$users = array_merge($users, $gh->getUserIDs($groupsTemp[$i]));
			}
		}
	}
	$users=array_values(array_unique($users));
	$appointments = null;
	
	$appointments = $ah->getSecretAppointmentArrayList($users, $startPeriodTS, $endPeriodTS);
	
	//add template for content
	$tpl->addBlockFile("CONTENT", "content", "tpl.cal_show_user_appointments.html");
	
	//add template for buttons
	$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
	
	$tpl->setCurrentBlock("btn_cell");
	$tpl->setVariable("BTN_LINK","javascript:close();");
	$tpl->setVariable("BTN_TXT","schliessen");
	$tpl->parseCurrentBlock();
	
	$tpl->touchBlock("btn_row");
	
	$tpl->setCurrentBlock("content");
	$tpl->setVariable("TXT_PAGEHEADLINE", "Diese Benutzer haben in dem Zeitraum <br><b>".
											$cal->addLeadingZero($startPeriodArray["mday"]).".".
											$cal->addLeadingZero($startPeriodArray["mon"]).".".
											$cal->addLeadingZero($startPeriodArray["year"])." ".
											$cal->addLeadingZero($startPeriodArray["hours"]).":".
											$cal->addLeadingZero($startPeriodArray["minutes"]). 
											"</b> bis <b>".
											$cal->addLeadingZero($endPeriodArray["mday"]).".".
											$cal->addLeadingZero($endPeriodArray["mon"]).".".
											$cal->addLeadingZero($endPeriodArray["year"])." ".
											$cal->addLeadingZero($endPeriodArray["hours"]).":".
											$cal->addLeadingZero($endPeriodArray["minutes"]). 
											"</b><br> bereits Termine eingetragen:");
	unset($i);
	if (!empty($appointments)) {
		foreach ($appointments as $row) {
			$css = (($i++)%2)==0 ? "even" : "uneven";
			$cdate = getDate($row->getStartTimestamp());
			$tpl->setCurrentBlock("appointment_row");
			$tpl->setVariable("DATE_STYLE", $css);
			$tpl->setVariable("DATE", $cal->getMappedShortWeekday($cdate["wday"]).", ".$cdate["mday"].". ".$cal->getShortMonth($cdate["mon"])." ".$cdate["year"]." ".$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"]));
			$tpl->setVariable("APPOINTMENT_STYLE", $css);
			$tpl->setVariable("APPOINTMENT", $row->getTerm());
			$tpl->parseCurrentBlock();
		}
	}
	else {
		$css = "even";
		$tpl->setCurrentBlock("appointment_row");
		$tpl->setVariable("DATE_STYLE", $css);
		$tpl->setVariable("DATE", "");
		$tpl->setVariable("APPOINTMENT_STYLE", $css);
		$tpl->setVariable("APPOINTMENT", "Keine Termine vorhanden.");
		$tpl->parseCurrentBlock();
	}
}

$tpl->show();

?>
