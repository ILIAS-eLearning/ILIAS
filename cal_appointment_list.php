<?php
/**
* Calendar - HalfYear overview
*
* @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
* @author MArtin Schumacher <ilias@auchich.de>
* @author Mark Ulbrich <Mark_Ulbrich@web.de>
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once("./classes/Calendar/class.ilCalendarTools.php");
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
$currentDay = date("d", $chosents2);
$startDay = date("d", $chosents);
if($currentDay<$startDay) {
	$tempTimestamp = strtotime("-1 month", $chosents2);
	$numOfDays = date("t", $tempTimestamp);
	$temp = getdate($tempTimestamp);
	$chosents2 = mktime($temp["hours"],$temp["minutes"],$temp["seconds"],$temp["mon"],$numOfDays,$temp["year"]);
}

$chosen2 = getdate($chosents2);
$today = getdate();
$todayts = mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]);

$weekdayFirstDay = $cal->getFirstWeekday($chosen["mon"], $chosen["year"] );

$ah = new ilAppointmentHandler();
$appointments = $ah->getAppointmentArrayList($ilias->account->getId(), $todayts, strtotime("+6 month", $todayts));

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.cal_appointment_list.html");

// display tabs
include "./include/inc.calendar_tabs.php";

$tpl->setCurrentBlock("content");

$tpl->setVariable("TXT_PAGEHEADLINE", "Semesterübersicht der Termine");
$tpl->setVariable("MINUS_YEAR", strtotime("-1 year", $chosents));
$tpl->setVariable("CHOSEN_YEAR", $chosen["year"]);
$tpl->setVariable("PLUS_YEAR", strtotime("+1 year", $chosents));
$tpl->setVariable("MINUS_MONTH", strtotime("-1 month", $chosents));
$tpl->setVariable("CHOSEN_MONTH", $cal->getMonth($chosen["mon"]));
$tpl->setVariable("CHOSENMONTHLINK", $chosents);
$tpl->setVariable("NEXT_MONTH", $cal->getMonth($chosen2["mon"]));
$tpl->setVariable("NEXTMONTHLINK", $chosents2);
$tpl->setVariable("PLUS_MONTH", strtotime("+1 month", $chosents));
$tpl->setVariable("GOTOTODAYLINK", $todayts);
$tpl->setVariable("TXT_GOTOTODAY", "heute");

unset($i);
if (!empty($appointments)) {
	foreach ($appointments as $row) {
		$css = (($i++)%2)==0 ? "even" : "uneven";
		$cdate = getDate($row->getStartTimestamp());
		$tpl->setCurrentBlock("appointment_row");
		$tpl->setVariable("DATE_STYLE", $css);
		$tpl->setVariable("DATE", $cal->getMappedShortWeekday($cdate["wday"]).", ".$cdate["mday"].". ".$cal->getShortMonth($cdate["mon"])." ".$cdate["year"]." ".$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"]));
		$tpl->setVariable("APPOINTMENT_STYLE", $css);
		$tpl->setVariable("APPOINTMENT", $cal->getSubstr($row->getTerm(), 80, $row));
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

for ($i=1;$i<=7;$i++) {
	$tpl->setCurrentBlock("firstWeekDays");
	$tpl->setVariable("FIRST_WEEKDAY", $cal->getShortWeekday($i));
	$tpl->parseCurrentBlock();
}

for ($i=1;$i<=7;$i++) {
	$tpl->setCurrentBlock("secondWeekDays");
	$tpl->setVariable("SECOND_WEEKDAY", $cal->getShortWeekday($i));
	$tpl->parseCurrentBlock();
}

// 1. Monat erste Zeile mit Tagen
				
$day = 1;
$newday = $cal->getNumOfDaysTS(strtotime("-1 month", $chosents))-($weekdayFirstDay-2);
$tpl->setVariable("WEEK_CSS1", "weeknumber");
$tpl->setVariable("WEEKLINK1", mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]));
$tpl->setVariable("WEEK1", $cal->getWeek(mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])));
$tpl->setCurrentBlock("dateRows");

for ($i=1;$i<=$weekdayFirstDay-1;$i++) {
	$tpl->setVariable("DATE_CSS1".$i, "prevMonth");
	$tpl->setVariable("FIRSTMONTHROWDATE".$i, $newday);
	$newday++;
}

for ($col=$weekdayFirstDay;$col<=7;$col++) {
	if ($day == $chosen["mday"]) {
		$tpl->setVariable("DATE_CSS1".$col, "chosenDate");
	}
	if ($day == $today["mday"]) {
		$tpl->setVariable("DATE_CSS1".$col, "today");
	}
	else {
		$tpl->setVariable("DATE_CSS1".$col, "date");
	}
	$tpl->setVariable("FIRSTMONTHROWDATELINK".$col, mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])); 
	$tpl->setVariable("FIRSTMONTHROWDATE".$col, $day);
	$day++;
}

$day2 = 1;
$newday2 = 1;
$tpl->setVariable("WEEK_CSS2", "weeknumber");
$tpl->setVariable("WEEKLINK2", mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"]));
$tpl->setVariable("WEEK2", $cal->getWeek(mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"])));
for ($i=1;$i<=$weekdayFirstDay2-1;$i++) {
	$tpl->setVariable("SECONDMONTHROWDATE".$i, "&nbsp;");
}
for ($col=$weekdayFirstDay2;$col<=7;$col++) {
	if ($day2 == $today["mday"]) {
		$tpl->setVariable("DATE_CSS2".$col, "today");
	}
	else {
		$tpl->setVariable("DATE_CSS2".$col, "date");
	}
	$tpl->setVariable("SECONDMONTHROWDATELINK".$col, mktime(0,0,0,$chosen2["mon"],$day2,$chosen["year"])); 
	$tpl->setVariable("SECONDMONTHROWDATE".$col, $day2);
	$day2++;
}

$tpl->parseCurrentBlock();

// printing the other rows
$daysLeft1 = $cal->getNumOfDaysTS($chosents)-($day-1);
$daysLeft2 = $cal->getNumOfDaysTS($chosents2)-($day2-1);
$daysLeft = $daysLeft1>$daysLeft2 ? $daysLeft1 : $daysLeft2;
$numOfRows = (($daysLeft - ($daysLeft % 7))/7)+((($daysLeft % 7)==0)?0:1);

for ($row=1;$row<=$numOfRows;$row++) {
	$tpl->setVariable("WEEK_CSS1", "weeknumber");
	$tpl->setVariable("WEEKLINK1", mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]));
	$tpl->setVariable("WEEK1", $cal->getWeek(mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])));
	$tpl->setCurrentBlock("dateRows");
	//<!-- 1. Monat -->
	for($col=1;$col<=7;$col++) {
		if ($day <= $cal->getNumOfDaysTS($chosents)) {
			if (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == $chosents) {
				$tpl->setVariable("DATE_CSS1".$col, "chosenDate");
			}
			elseif (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == $todayts) {
				$tpl->setVariable("DATE_CSS1".$col, "today");
			}
			else {
				$tpl->setVariable("DATE_CSS1".$col, "date");
			}
			$tpl->setVariable("FIRSTMONTHROWDATELINK".$col, mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])); 
			$tpl->setVariable("FIRSTMONTHROWDATE".$col, $day);
		}
		else {
			$tpl->setVariable("FIRSTMONTHROWDATE".$col, "&nbsp;");
		}
		$day++;
	}
	
	//<!-- 2. Monat -->
	$tpl->setVariable("WEEK_CSS2", "weeknumber");
	$tpl->setVariable("WEEKLINK2", mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"]));
	$tpl->setVariable("WEEK2", $cal->getWeek(mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"])));
	for($col=1;$col<=7;$col++) {
		if ($day2 <= $cal->getNumOfDaysTS($chosents2)) {
			$tpl->setVariable("DATE_CSS2".$col, "date");
			$tpl->setVariable("SECONDMONTHROWDATELINK".$col, mktime(0,0,0,$chosen2["mon"],$day2,$chosen["year"])); 
			$tpl->setVariable("SECONDMONTHROWDATE".$col, $day2);
		}
		else {
			$tpl->setVariable("DATE_CSS2".$col, "prevMonth");
			$tpl->setVariable("SECONDMONTHROWDATE".$col, $newday2);
			$newday2++;
		}
		$day2++;
	}
	$tpl->parseCurrentBlock();
}

$tpl->show();

?>
