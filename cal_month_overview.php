<?php
/**
* Calendar - month overview
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
$weekdayFirstDay2 = $cal->getFirstWeekday($chosen2["mon"], $chosen2["year"] );

$ah = new ilAppointmentHandler();
$appointments = $ah->getAppointmentArrayList($ilias->account->getId(),
  															mktime(0,0,0,$chosen["mon"],1,$chosen["year"]),
  															mktime(23,59,59,$chosen["mon"],$cal->getNumOfDays($chosen["mon"], $chosen["year"]),$chosen["year"]));

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.cal_month_overview.html");
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
$tpl->setVariable("ITEM", $lng->txt("calendar")." (".$lng->txt("month").")");
$tpl->setVariable("LINK_ITEM", "cal_month_overview.php?ts=".$chosents);
$tpl->parseCurrentBlock();



$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $cal->getMappedWeekday($chosen["wday"]).", ".
				 $chosen["mday"].".".$cal->getMonth($chosen["mon"])." ".$chosen["year"]);

$tpl->setVariable("MINUS_YEAR", strtotime("-1 year", $chosents));
$tpl->setVariable("CHOSEN_YEAR", $chosen["year"]);
$tpl->setVariable("PLUS_YEAR", strtotime("+1 year", $chosents));
$tpl->setVariable("MINUS_MONTH", strtotime("-1 month", $chosents));
$tpl->setVariable("CHOSEN_MONTH", $cal->getMonth($chosen["mon"]));
$tpl->setVariable("NEXT_MONTH", $cal->getMonth($chosen2["mon"]));
$tpl->setVariable("PLUS_MONTH", strtotime("+1 month", $chosents));
$tpl->setVariable("GOTOTODAYLINK", $todayts);
$tpl->setVariable("TXT_GOTOTODAY", "heute");

for ($i=1;$i<=7;$i++) {
	$tpl->setCurrentBlock("weekday");
	$tpl->setVariable("WEEKDAY", $cal->getShortWeekday($i));
	$tpl->parseCurrentBlock();
}

$day = 1;
$newday = $cal->getNumOfDaysTS(strtotime("-1 month", $chosents))-($weekdayFirstDay-2);
$newday2 = 1;
$tpl->setCurrentBlock("day_row");
$tpl->setVariable("WEEKLINK", mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]));
$tpl->setVariable("WEEKNUMBER", $cal->getWeek(mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])).". Wo");

for ($i=1;$i<=$weekdayFirstDay-1;$i++) {
	$tpl->setVariable("DATELINK".$i, mktime(0,0,0,$chosen["mon"]-1,$newday,$chosen["year"]));
	$tpl->setVariable("ADDLINK".$i, mktime(0,0,0,$chosen["mon"]-1,$newday,$chosen["year"]));
	$tpl->setVariable("APPOINTMENT_STYLE".$i, "prevMonthMO");
	$tpl->setVariable("DATE".$i, $newday);
	$tpl->setVariable("APPOINTMENT".$i, "&nbsp;");
	$newday++;
}

for ($col=$weekdayFirstDay;$col<=7;$col++) {
	if (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == $chosents) {
		$tpl->setVariable("APPOINTMENT_STYLE".$col, "chosenDateMO");
		$tpl->setVariable("APPOINTMENT_STYLE1".$col, "chosenDateMO");
	}
	elseif (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == $todayts) {
		$tpl->setVariable("APPOINTMENT_STYLE".$col, "todayMO");
		$tpl->setVariable("APPOINTMENT_STYLE1".$col, "todayMO");
	}
	else {
		$tpl->setVariable("APPOINTMENT_STYLE".$col, "dateMO");
		$tpl->setVariable("APPOINTMENT_STYLE1".$col, "dateMO");
	}
	$tpl->setVariable("DATELINK".$col, mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])); 
	$tpl->setVariable("ADDLINK".$col, mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])); 
	$tpl->setVariable("DATE".$col, $day);
	$appstr = "";
	if (count($appointments)>0) {
		foreach ($appointments as $row) {
			$cdate = getDate($row->getStartTimestamp());
			if (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == mktime(0,0,0,$cdate["mon"],$cdate["mday"],$cdate["year"])) {
				$appstr = $appstr.$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"])." ".$cal->getSubstr($row->getTerm(),20, $row)."<br>"; 
			}
		}
	}
	$tpl->setVariable("APPOINTMENT".$col, $appstr);
	$day++;
}

$tpl->parseCurrentBlock();

// printing the other rows
$daysLeft = $cal->getNumOfDaysTS($chosents)-($day-1);
$numOfRows = (($daysLeft - ($daysLeft % 7))/7)+((($daysLeft % 7)==0)?0:1);
for ($row=1;$row<=$numOfRows;$row++) {
	$tpl->setCurrentBlock("day_row");
	$tpl->setVariable("WEEKLINK", mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]));
	$tpl->setVariable("WEEKNUMBER", $cal->getWeek(mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])).". Wo");
	for($col=1;$col<=7;$col++) {
		if ($day <= $cal->getNumOfDaysTS($chosents)) {
			if (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == $chosents) {
				$tpl->setVariable("APPOINTMENT_STYLE".$col, "chosenDateMO");
				$tpl->setVariable("APPOINTMENT_STYLE1".$col, "chosenDateMO");
			}
			elseif (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == $todayts) {
				$tpl->setVariable("APPOINTMENT_STYLE".$col, "todayMO");
				$tpl->setVariable("APPOINTMENT_STYLE1".$col, "todayMO");
			}
			else {
				$tpl->setVariable("APPOINTMENT_STYLE".$col, "dateMO");
				$tpl->setVariable("APPOINTMENT_STYLE1".$col, "dateMO");
			}
			$tpl->setVariable("DATELINK".$col, mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])); 
			$tpl->setVariable("ADDLINK".$col, mktime(0,0,0,$chosen["mon"],$day,$chosen["year"])); 
			$tpl->setVariable("DATE".$col, $day);
			$appstr = "";
			if(count($appointments)>0) {
				foreach ($appointments as $app) {
					$cdate = getdate($app->getStartTimestamp());
					if (mktime(0,0,0,$chosen["mon"],$day,$chosen["year"]) == mktime(0,0,0,$cdate["mon"],$cdate["mday"],$cdate["year"])) {
						$appstr = $appstr.$cal->addLeadingZero($cdate["hours"]).":".$cal->addLeadingZero($cdate["minutes"])." ".
										$cal->getSubstr($app->getTerm(), 20, $app)."<br>"; 
					}
				}
			}
			$tpl->setVariable("APPOINTMENT".$col, $appstr);
		}
		else {
			$tpl->setVariable("DATELINK".$col, mktime(0,0,0,$chosen["mon"]+1,$newday2,$chosen["year"]));
			$tpl->setVariable("ADDLINK".$col, mktime(0,0,0,$chosen["mon"]+1,$newday2,$chosen["year"]));
			$tpl->setVariable("APPOINTMENT_STYLE".$col, "prevMonthMO");
			$tpl->setVariable("DATE".$col, $newday2);
			$tpl->setVariable("APPOINTMENT".$col, "&nbsp;");
			$newday2++;
		}
		$day++;
	}
	
	$tpl->parseCurrentBlock();
}
$tpl->show();

?>
