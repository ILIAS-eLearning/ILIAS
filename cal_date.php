<?php
/**
* Calendar - day overview
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
require_once "./classes/Calendar/class.ilCalDBHandler.php";
			
$cal = new ilCalendarTools();
$ah = new ilAppointmentHandler();

$today = getdate();
$todayts = mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]);
/*
 * Fill the database and empty the database for testing purposes
 *
 */
/*
if ($_GET["fillDB"] == "true") {
	$userArr = array(0 => $ilias->account->getId());
	//echo "UserId: ".$ilias->account->getId();

	for ($i=0; $i<=10; $i++) {
		$appointment = new ilAppointment();
		$appointment->setAccess("Private");
		$appointment->setCategoryId(1);
		$appointment->setDescription("");
		$appointment->setDuration(40);
		$appointment->setPriorityId(1);
		$ats = mktime(mt_rand(6, 20), mt_rand(0, 59), 0, mt_rand($today["mon"]+1, $today["mon"]+5), mt_rand(1, 31), 2003);
		$appointment->setStartTimestamp($ats);
		//$appointment->setStartTimestamp(mktime(mt_rand(6, 20), mt_rand(0, 59), 0, 5, mt_rand(1, 31), 2003));
		$appointment->setTerm("(".$cal->addLeadingZero($i).") Dies ist ein langer Termin (".date("d.m.", $ats).")");
		$appointment->setOwnerId($ilias->account->getId());
		$appointment->setLocation("");
		$appointment->setSerial(0);

		$ah->insertAppointment($userArr, $appointment);
	}

	$appointment = new ilAppointment();
	$appointment->setAccess("Private");
	$appointment->setCategoryId(1);
	$appointment->setDescription("");
	$appointment->setDuration(48*60);		
	$appointment->setPriorityId(1);
	$appointment->setStartTimestamp(mktime(12, 0, 0, $today["mon"], 15, 2003));
	$appointment->setTerm("Dies ist ein Termin mit 48 stündiger Laufzeit");
	$appointment->setOwnerId($ilias->account->getId());
	$appointment->setLocation("");
	$appointment->setSerial(0);
	$ah->insertAppointment($userArr, $appointment);
	
	
	$appointment = new ilAppointment();
	$appointment->setAccess("Private");
	$appointment->setCategoryId(1);
	$appointment->setDescription("");
	$appointment->setDuration(40);		
	$appointment->setPriorityId(1);
	$appointment->setStartTimestamp(mktime(mt_rand(6, 20), mt_rand(0, 59), 0, $today["mon"], 15, 2003));
	$appointment->setTerm("Dies ist ein MONATLICHER Wiederholungstermin ");
	$appointment->setOwnerId($ilias->account->getId());
	$appointment->setLocation("");
	$appointment->setSerial(1);
	$appointment->setSer_type("ser_month");
	$appointment->setSer_stop(mktime(23, 59, 0, $today["mon"], 31, 2004));
	
	$ah->insertAppointment($userArr, $appointment);
	
	$appointment = new ilAppointment();
	$appointment->setAccess("Private");
	$appointment->setCategoryId(1);
	$appointment->setDescription("");
	$appointment->setDuration(40);		
	$appointment->setPriorityId(1);
	$appointment->setStartTimestamp(mktime(mt_rand(6, 20), mt_rand(0, 59), 0, $today["mon"], 15, 2003));
	$appointment->setTerm("Dies ist ein HALBJÄHRLICHER Wiederholungstermin ");
	$appointment->setOwnerId($ilias->account->getId());
	$appointment->setLocation("");
	$appointment->setSerial(1);
	$appointment->setSer_type("ser_halfayear");
	$appointment->setSer_stop(mktime(23, 59, 0, $today["mon"], 31, 2004));
	
	$ah->insertAppointment($userArr, $appointment);
	
	$appointment = new ilAppointment();
	$appointment->setAccess("Private");
	$appointment->setCategoryId(1);
	$appointment->setDescription("");
	$appointment->setDuration(40);		
	$appointment->setPriorityId(1);
	$appointment->setStartTimestamp(mktime(mt_rand(6, 20), mt_rand(0, 59), 0, $today["mon"], 15, 2003));
	$appointment->setTerm("Dies ist ein JÄHRLICHER Wiederholungstermin ");
	$appointment->setOwnerId($ilias->account->getId());
	$appointment->setLocation("");
	$appointment->setSerial(1);
	$appointment->setSer_type("ser_year");
	$appointment->setSer_stop(mktime(23, 59, 0, $today["mon"], 31, 2004));
	
	$ah->insertAppointment($userArr, $appointment);	
}

if ($_GET["deleteDB"] == "true")
{
	$dbh = new ilCalDBHandler();
	$dbh->delete("cal_appointment", "", true);
	$dbh->delete("cal_appointmentrepeats", "", true);
	$dbh->delete("cal_appointmentrepeatsnot", "", true);
}
*/

if (isset($_POST["selYear"])) {
	$chosents = mktime(0,0,0,$_POST["selMonth"],$_POST["selDay"],$_POST["selYear"]);
}
elseif ($_GET["ts"] != "") {
	$chosents = $_GET["ts"];
	$chosen = getdate($chosents);
}
elseif ($_GET["date"] != "" && $_GET["month"] != "" && $_GET["year"] != "") {
	$chosen = getdate(mktime(0,0,0,$_GET["month"],$_GET["date"],$_GET["year"]));
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

$weekdayFirstDay = $cal->getFirstWeekday($chosen["mon"], $chosen["year"] );
$weekdayFirstDay2 = $cal->getFirstWeekday($chosen2["mon"], $chosen2["year"] );

$appointments = $ah->getAppointmentArrayList($ilias->account->getId(), mktime(0,0,0,$chosen["mon"],$chosen["mday"],$chosen["year"]), mktime(23,59,59,$chosen["mon"],$chosen["mday"],$chosen["year"]));
//echo "count(appointments): ".count($appointments)."<br>";

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.cal_date.html");

//add template for buttons
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

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
$tpl->setVariable("ITEM", $lng->txt("calendar")." (".$lng->txt("day").")");
$tpl->setVariable("LINK_ITEM", "cal_date.php?ts=".$chosents);
$tpl->parseCurrentBlock();


/*
 * Buttons for filling and emptying the database for testing purposes
 */
/*
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","cal_date.php?fillDB=true");
$tpl->setVariable("BTN_TXT","Fülle Datenbank");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","cal_date.php?deleteDB=true");
$tpl->setVariable("BTN_TXT","leere Datenbank");
$tpl->parseCurrentBlock();
$tpl->touchBlock("btn_row");
*/


$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $cal->getMappedWeekday($chosen["wday"]).", ".
				 $chosen["mday"].".".$cal->getMonth($chosen["mon"])." ".$chosen["year"]);

$tpl->setVariable("TXT_TIME", $lng->txt("time"));
$tpl->setVariable("TXT_APPOINTMENT", $lng->txt("appointment"));
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

for ($i=0;$i<=23;$i++) {
	$css = ($i%2)==0 ? "even" : "uneven";
	$tpl->setCurrentBlock("time_row");
	$tpl->setVariable("ROW_TIME", $cal->addLeadingZero($i).".00");
	$tpl->setVariable("APPOINTMENT_STYLE", $css);
	$appstr = "";
	//echo "count: ".count($appointments)."<br>";
	if(count($appointments)>0) {
		foreach ($appointments as $row) {
			$cdate = getDate($row->getStartTimestamp());
			if ($cal->compareTSHour($row->getStartTimestamp(), mktime($i,0,0,$chosen["mon"],$chosen["mday"],$chosen["year"])) == TRUE) { 
				$appstr = $appstr.$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"])." ".$cal->getSubstr($row->getTerm(), 60, $row)."<br>"; 
			}
		}
	}
	$tpl->setVariable("ROW_APPOINTMENT", $appstr);
	$tpl->parseCurrentBlock();
}

$appDate = $chosen;
$counter = 1;
while ($counter <= 31) {
	if ($appDate["mday"] == $counter) {
		$checked = "selected";
	}
     $count_txt = $count_txt . "<OPTION VALUE=\"{$counter}\" {$checked}>".$cal->addLeadingZero($counter)."</OPTION>";
     $checked = "";
     $counter = $counter + 1;
}
$tpl->setVariable("VAL_day_count", $count_txt);
$count_txt = "";
$counter = 1;
while ($counter <= 12) {
	if ($appDate["mon"] == $counter) {
		$checked = "selected";
	}
	$count_txt = $count_txt . "<OPTION VALUE=\"{$counter}\" {$checked}>".$cal->addLeadingZero($counter)."</OPTION>";
	$checked = "";
	$counter = $counter + 1;
}
$tpl->setVariable("VAL_month_count", $count_txt);
$tpl->setVariable("VAR_Year_act", date("Y"));

if ($appDate["year"] == date("Y"))
	$tpl->setVariable("VAL_Year_act_sel", "selected");	

$tpl->setVariable("VAR_Year_nxt", date("Y", strtotime("+1 year")));

if ($appDate["year"] == date("Y", strtotime("+1 year")))
	$tpl->setVariable("VAL_Year_nxt_sel", "selected");	

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
	$tpl->setVariable("FIRSTMONTHROWDATELINK".$i, mktime(0,0,0,$chosen["mon"]-1,$newday,$chosen["year"])); 
	$tpl->setVariable("FIRSTMONTHROWDATE".$i, $newday);
	$newday++;
}
for ($col=$weekdayFirstDay;$col<=7;$col++)
{
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
	$tpl->setVariable("DATE_CSS2".$col, "date");
	$tpl->setVariable("SECONDMONTHROWDATELINK".$col, mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"])); 
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
		if ($day <= $cal->getNumOfDaysTS($chosents))  {
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
	
	if ($cal->compareTSDate($chosents, mktime(0,0,0,2,29,2004))) {
		$comment = "<font color=\"red\"><b>WIR HABEN UNSER DIPLOM!!</b></font>";
	}
	
	//<!-- 2. Monat -->
	$tpl->setVariable("WEEK_CSS2", "weeknumber");
	$tpl->setVariable("WEEKLINK2", mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"]));
	$tpl->setVariable("WEEK2", $cal->getWeek(mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"])));
	for($col=1;$col<=7;$col++) {
		if ($day2 <= $cal->getNumOfDaysTS($chosents2)) {
			$tpl->setVariable("DATE_CSS2".$col, "date");
			$tpl->setVariable("SECONDMONTHROWDATELINK".$col, mktime(0,0,0,$chosen2["mon"],$day2,$chosen2["year"])); 
			$tpl->setVariable("SECONDMONTHROWDATE".$col, $day2);
		}
		else {
			$tpl->setVariable("DATE_CSS2".$col, "prevMonth");
			$tpl->setVariable("SECONDMONTHROWDATELINK".$col, mktime(0,0,0,$chosen2["mon"]+1,$newday2,$chosen2["year"])); 
			$tpl->setVariable("SECONDMONTHROWDATE".$col, $newday2);
			$newday2++;
		}
		$day2++;
	}
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("COMMENT", $comment);

$tpl->show();

?>
