<?php

/**
 * Canlendar-Tools
 * The CanlendarTools provides utility-functions to the ILIAS-Calendar-Project 
 * like mapping weekdays for german calendars or comparing dates.
 *
 * version 1.0
 * @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
 * @author MArtin Schumacher <ilias@auchich.de>
 * @author Mark Ulbrich <Mark_Ulbrich@web.de>
 **/

require_once "./classes/Calendar/class.ilAppointment.php";

class CalendarTools
{
	var $months = array(1 => "Januar",
						2 => "Februar",
						3 => "M&auml;rz",
						4 => "April",
						5 => "Mai",
						6 => "Juni",
						7 => "Juli",
						8 => "August",
						9 => "September",
						10 => "Oktober",
						11 => "November",
						12 => "Dezember");
						
	var $shortMonths = array(1 => "Jan",
						2 => "Feb",
						3 => "Mrz",
						4 => "Apr",
						5 => "Mai",
						6 => "Jun",
						7 => "Jul",
						8 => "Aug",
						9 => "Sep",
						10 => "Okt",
						11 => "Nov",
						12 => "Dez");
	 
	var $weekdays = array(1 => "Montag",
		 			      2 => "Dienstag",
					      3 => "Mittwoch",
					      4 => "Donnerstag",
				    	  5 => "Freitag",
					      6 => "Samstag",
						  7 => "Sonntag",);
						  
	var $shortWeekdays = array(1 => "Mo",
							   		2 => "Di",
							   		3 => "Mi",
							   		4 => "Do",
							  			5 => "Fr",
							   		6 => "Sa",
							   		7 => "So");
							   
	var $daymapping = array(7,1,2,3,4,5,6);
	
	function getMonth($month) {
		return $this->months[$month];
	}
	
	function getShortMonth($month) {
		return $this->shortMonths[$month];
	}
	
	function getMappedWeekday($wday) {
		return $this->weekdays[$this->daymapping[$wday]];
	}
	
	function getMappedShortWeekday($wday) {
		return $this->shortWeekdays[$this->daymapping[$wday]];
	}
	
	function getWeekday($wday) {
		return $this->weekdays[$wday];
	}
	
	function getShortWeekday($wday) {
		return $this->shortWeekdays[$wday];
	}
	
	function getNumOfDays($month, $year) {
		return date("t", mktime(0,0,0,$month,1,$year));
	}
	
	function getNumOfDaysTS($timestamp) {
		return date("t", $timestamp);
	}
	
	function getFirstWeekday($month, $year=0) {
		if ($year == 0) 
			$year=date("Y");
		$weekdayFirstDay = date("w", mktime(0,0,0,$month,1,$year));
		if ($weekdayFirstDay == 0) 
			$weekdayFirstDay = 7;
		return $weekdayFirstDay;
	}
	
	function getBeginningDay($chosenDateTS) {		
		if(date("w", $chosenDateTS) == 1) 
			return $chosenDateTS;
		else
			return strtotime("last Monday", $chosenDateTS);
	}
	
	function addLeadingZero($number) {
		if ($number < 10)
			return "0".$number;
		else
			return $number;
	}
	
	function getWeek($ts) {
		return date("W", $ts);
	}
	
	function compareTSYear($ts1, $ts2) {
		$tsa1 = getdate($ts1);
		$tsa2 = getdate($ts2);
		if ($tsa1["year"] == $tsa2["year"])
			return TRUE;
		else
			return FALSE;
	}
	
	function compareTSMonth($ts1, $ts2) {
		$tsa1 = getdate($ts1);
		$tsa2 = getdate($ts2);
		if (   $tsa1["year"] == $tsa2["year"] 
		    && $tsa1["mon"] == $tsa2["mon"] )
			return TRUE;
		else
			return FALSE;
	}
	
	function compareTSDate($ts1, $ts2) {
		$tsa1 = getdate($ts1);
		$tsa2 = getdate($ts2);
		if (   $tsa1["year"] == $tsa2["year"] 
		    && $tsa1["mon"] == $tsa2["mon"] 
		    && $tsa1["mday"] == $tsa2["mday"])
			return TRUE;
		else
			return FALSE;
	}
	
	function compareTSHour($ts1, $ts2) {
		$tsa1 = getdate($ts1);
		$tsa2 = getdate($ts2);
		if (   $tsa1["year"] == $tsa2["year"] 
		    && $tsa1["mon"] == $tsa2["mon"] 
		    && $tsa1["mday"] == $tsa2["mday"]
		    && $tsa1["hours"] == $tsa2["hours"])
			return TRUE;
		else
			return FALSE;
	}
	
	function compareTSMinute($ts1, $ts2) {
		$tsa1 = getdate($ts1);
		$tsa2 = getdate($ts2);
		if (   $tsa1["year"] == $tsa2["year"] 
		    && $tsa1["mon"] == $tsa2["mon"] 
		    && $tsa1["mday"] == $tsa2["mday"]
		    && $tsa1["hours"] == $tsa2["hours"]
		    && $tsa1["minutes"] == $tsa2["minutes"])
			return TRUE;
		else
			return FALSE;
	}
	
	function getSubstr($str, $stop=1, $appointment) {
		if (strlen($str) > $stop)
			$str = substr($str, 0, $stop-3)."...";
		
		if (!(strpos($str, "<i>[E]</i>") === false) || !(strpos($str, "<i>[M]</i>") === false)) {
			$link = $str;
		}
		else {
			$link = "<a href=\"cal_edit_entry.php?aid=".$appointment->getAppointmentId()."&ts=".$appointment->getStartTimestamp()."\" target=\"bottom\">".$str."</a>";
		}
		return $link;
	}
	
	function isNumeric($str) {
		$isNumeric = 0;
		for ($i=0;$i<strlen($str);$i++) {
			switch($str{$i}) {
				case "0":
				case "1":
				case "2":
				case "3":
				case "4":
				case "5":
				case "6":
				case "7":
				case "8":
				case "9":
					$isNumeric++;
					break;
			}
		}
		return ($isNumeric == strlen($str));
	}
}
 
?>
