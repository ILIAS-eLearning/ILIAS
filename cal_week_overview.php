<?php
/**
* Calendar - week overview
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
			
$cal = new ilCalendarTools();
if ($_GET["ts"] != "") {
	$chosents = $_GET["ts"];
	$chosen = getdate($chosents);
}
elseif ($_GET["date"] != "" && $_GET["month"] != "" && $_GET["year"] != "") {
	$chosen = getdate(mktime(0,0,0,$_GET["date"],$_GET["month"],$_GET["year"]));
	$chosents = mktime(0,0,0,$chosen["mon"],$chosen["mday"],$chosen["year"]);
}
else {
	$chosen = getdate();
	$chosents = mktime(0,0,0,$chosen["mon"],$chosen["mday"],$chosen["year"]);
}

$chosents2 = strtotime("+1 month", $chosents);
$chosen2 = getdate($chosents2);
$today = getdate();
$todayts = mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]);

$weekdayFirstDay = $cal->getFirstWeekday($chosen["mon"], $chosen["year"] );
$beginningDayts = $cal->getBeginningDay($chosents);
$bd = getDate($beginningDayts);
$beginningDay = array("mday" => array(1 => date("d", strtotime("", $beginningDayts)),
												  2 => date("d", strtotime("+1 day", $beginningDayts)),
												  3 => date("d", strtotime("+2 days", $beginningDayts)),
												  4 => date("d", strtotime("+3 days", $beginningDayts)),
												  5 => date("d", strtotime("+4 days", $beginningDayts)),
												  6 => date("d", strtotime("+5 days", $beginningDayts)),
												  7 => date("d", strtotime("+6 days", $beginningDayts))),
							 "mon"  => array(1 => date("n", strtotime("", $beginningDayts)),
												  2 => date("n", strtotime("+1 day", $beginningDayts)),
												  3 => date("n", strtotime("+2 days", $beginningDayts)),
												  4 => date("n", strtotime("+3 days", $beginningDayts)),
												  5 => date("n", strtotime("+4 days", $beginningDayts)),
												  6 => date("n", strtotime("+5 days", $beginningDayts)),
												  7 => date("n", strtotime("+6 days", $beginningDayts))),
							 "ts"   => array(1 => strtotime("", $beginningDayts),
												  2 => strtotime("+1 day", $beginningDayts),
												  3 => strtotime("+2 days", $beginningDayts),
												  4 => strtotime("+3 days", $beginningDayts),
												  5 => strtotime("+4 days", $beginningDayts),
												  6 => strtotime("+5 days", $beginningDayts),
												  7 => strtotime("+6 days", $beginningDayts)));

$ah = new ilAppointmentHandler();
$appointments = $ah->getAppointmentArrayList($ilias->account->getId(), $beginningDay["ts"][1], mktime(23,59,59,$beginningDay["mon"][7],$beginningDay["mday"][7],$chosen["year"]));

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.cal_week_overview.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

// display tabs
include "./include/inc.calendar_tabs.php";

// set locator
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

$tpl->touchBlock("locator_separator");

$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("calendar")." (".$lng->txt("week").")");
$tpl->setVariable("LINK_ITEM", "cal_week_overview.php?ts=".$chosents);
$tpl->parseCurrentBlock();


$tpl->setCurrentBlock("content");

$tpl->setVariable("TXT_PAGEHEADLINE", $cal->getMappedWeekday($chosen["wday"]).", ".
				 $chosen["mday"].".".$cal->getMonth($chosen["mon"])." ".$chosen["year"]);


$tpl->setVariable("MINUS_YEAR", strtotime("-1 year", $chosents));
$tpl->setVariable("CHOSEN_YEAR", $chosen["year"]);
$tpl->setVariable("PLUS_YEAR", strtotime("+1 year", $chosents));
$tpl->setVariable("MINUS_MONTH", strtotime("-1 month", $chosents));
$tpl->setVariable("CHOSEN_MONTH", $cal->getMonth($chosen["mon"]));
$tpl->setVariable("CHOSENMONTHLINK", $chosents);
$tpl->setVariable("PLUS_MONTH", strtotime("+1 month", $chosents));
$tpl->setVariable("MINUS_WEEK", strtotime("-1 week", $chosents));
$tpl->setVariable("CHOSEN_WEEK", $cal->getWeek($chosents).". Woche");
$tpl->setVariable("PLUS_WEEK", strtotime("+1 week", $chosents));
$tpl->setVariable("GOTOTODAYLINK", $todayts);
$tpl->setVariable("TXT_GOTOTODAY", "heute");

for ($i=1;$i<=7;$i++) {
	$tpl->setCurrentBlock("weekday");
	$tpl->setVariable("WEEKDAYLINK", $beginningDay["ts"][$i]);
	$tpl->setVariable("WEEKDAY", $cal->getShortWeekday($i).", ".$beginningDay["mday"][$i].".".$cal->getShortMonth($beginningDay["mon"][$i]));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("time_row");
$tpl->setVariable("TIME_STYLE", "weekTime");
$tpl->setVariable("TIME", "00.00<br>- 07.00");
for($ii=1;$ii<=7;$ii++) {
	if ($beginningDay["ts"][$ii] == $chosents) {
		$tpl->setVariable("APPOINTMENT_STYLE".$ii, "chosenDate");
	}
	elseif ($beginningDay["ts"][$ii] == $todayts) {
		$tpl->setVariable("APPOINTMENT_STYLE".$ii, "today");
	}
	else {
		$tpl->setVariable("APPOINTMENT_STYLE".$ii, "date");
	}
	
	$appstr = "";
	if(count($appointments)>0) {
		foreach ($appointments as $row) {
			$cdate = getDate($row->getStartTimestamp());
			for($hour=0;$hour<7;$hour++) {
				if ($cal->compareTSHour($row->getStartTimestamp(), mktime($hour,0,0,$beginningDay["mon"][$ii],$beginningDay["mday"][$ii],$chosen["year"])) == TRUE){
					$appstr = $appstr.$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"])."<br>&nbsp;".$cal->getSubstr($row->getTerm(), 25, $row)."<br>"; 
				}
			}
		}
	}
	else {
		$appstr = "";
	}
	$tpl->setVariable("APPOINTMENT".$ii, $appstr);
}
$tpl->parseCurrentBlock();	

for ($i=7;$i<19;$i++) {
	$tpl->setCurrentBlock("time_row");
	$css = $i%2==0 ? "evenMO" : "unevenMO";
	$tpl->setVariable("TIME_STYLE", $css);
	$tpl->setVariable("TIME", $cal->addLeadingZero($i).".00");
	for($ii=1;$ii<=7;$ii++) {
		if ($beginningDay["ts"][$ii] == $chosents) {
			$tpl->setVariable("APPOINTMENT_STYLE".$ii, "chosenDate");
		}
		elseif ($beginningDay["ts"][$ii] == $todayts) {
			$tpl->setVariable("APPOINTMENT_STYLE".$ii, "today");
		}
		else {
			$tpl->setVariable("APPOINTMENT_STYLE".$ii, $css);
		}
		$appstr = "";
		if(count($appointments)>0) {
			foreach ($appointments as $row) {
				$cdate = getDate($row->getStartTimestamp());
				if ($cal->compareTSHour($row->getStartTimestamp(), mktime($i,0,0,$beginningDay["mon"][$ii],$beginningDay["mday"][$ii],$chosen["year"])) == TRUE){
					$appstr = $appstr.$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"])."<br>&nbsp;".$cal->getSubstr($row->getTerm(), 25, $row)."<br>"; 
				}
			}
		}
		$tpl->setVariable("APPOINTMENT".$ii, $appstr);
	}
	$tpl->parseCurrentBlock();	
}

$tpl->setCurrentBlock("time_row");
$tpl->setVariable("TIME_STYLE", "weekTime");
$tpl->setVariable("TIME", "19.00<br>- 00.00");
for($ii=1;$ii<=7;$ii++) {
	if ($beginningDay["ts"][$ii] == $chosents) {
		$tpl->setVariable("APPOINTMENT_STYLE".$ii, "chosenDate");
	}
	elseif ($beginningDay["ts"][$ii] == $todayts) {
		$tpl->setVariable("APPOINTMENT_STYLE".$ii, "today");
	}
	else {
		$tpl->setVariable("APPOINTMENT_STYLE".$ii, "date");
	}
	$appstr = "";
	if(count($appointments)>0) {
		foreach ($appointments as $row) {
			$cdate = getDate($row->getStartTimestamp());
			for($hour=19;$hour<=23;$hour++) {
				if ($cal->compareTSHour($row->getStartTimestamp(), mktime($hour,0,0,$beginningDay["mon"][$ii],$beginningDay["mday"][$ii],$chosen["year"])) == TRUE) {
					$appstr = $appstr.$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"])."<br>&nbsp;".$cal->getSubstr($row->getTerm(), 25, $row)."<br>"; 
				}
			}
		}
	}
	$tpl->setVariable("APPOINTMENT".$ii, $appstr);
}
$tpl->parseCurrentBlock();	

$tpl->show();

?>
