<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source															  |
	|	Dateplaner Modul														  |													
	+-----------------------------------------------------------------------------+
	| Copyright (c) 2004 ILIAS open source & University of Applied Sciences Bremen|
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Timestamp to Date Class
*
* this class should manage the timestamp functions. eg. formating
* Diese Klassse ermöglicht das formatierten eines timestamps.   
*
* @author       Frank Gruemmert <gruemmert@feuerwelt.de>    
* @version      $Id$                                    
* @module       class.TimestampToDate.php                            
* @modulegroup  dateplaner                    
* @package		dateplaner-backend
*/

class TimestampToDate {
	
	
		/**
		* function ttd($timestamp)
		* @description : send formated timstamp strings back
		* @param int timestamp
		* @global Array DP_language ( iunclude Langeproperties )
		* @return object
		*/

	    function ttd($timestamp) {

			global $DP_language;

	    	$tdd		=  date("d:w:m:Y:H:i:s",$timestamp);

    	  	$months		= array("01"=>$DP_language[long_01],"02"=>$DP_language[long_02],"03"=>$DP_language[long_03],"04"=>$DP_language[long_04],"05"=>$DP_language[long_05],"06"=>$DP_language[long_06],"07"=>$DP_language[long_07],"08"=>$DP_language[long_08],"09"=>$DP_language[long_09],"10"=>$DP_language[long_10],"11"=>$DP_language[long_11],"12"=>$DP_language[long_12]);

        	$days		=   array("0"=>$DP_language[Mo_long],"1"=>$DP_language[Tu_long],"2"=>$DP_language[We_long],"3"=>$DP_language[Th_long],"4"=>$DP_language[Fr_long],"5"=>$DP_language[Sa_long],"6"=>$DP_language[Su_long]);

	        list($monthsday, $day,$month,$year,$hour,$minute,$second) = explode(":",$tdd);

			$this->addEng			= date("S",$timestamp);
			$this->weekday			= $days[$day-1];
			$this->weeknumber		= date("W",$timestamp);
			$this->weekdaynumber	= date("w",$timestamp);
			$this->monthname		= $months[$month];
			$this->monthnumber		= date("n",$timestamp);
			$this->monthnumber_long	= date("m",$timestamp);
			$this->hour_long		= date("H",$timestamp);
			$this->hour_short		= date("G",$timestamp);
			$this->minutes			= date("i",$timestamp);
			$this->seconds			= date("s",$timestamp);
			$this->day_of_month		= date("d",$timestamp);
			$this->day_of_month_short = date("j",$timestamp);
			$this->day_of_year		= date("z",$timestamp)+1;
			$this->days_in_month	= date("t",$timestamp);
			$this->year_long		= date("Y",$timestamp);
			$this->year_short		= date("y",$timestamp);
			$this->day_ampm			= date("a",$timestamp);
			$leapyear				= (date("L",$timestamp)) ? "0" : "1";
			$this->leapyear			= $leapyear; // schaltjahr

			$Date_format_middle		= $DP_language[date_format_middle];
			switch($Date_format_middle) {
				case 'd/m/y':
					$this->longtime			= "$this->day_of_month_short. $months[$month] $year / $hour:$minute:$second $sl_language[hour] ";
	       			$this->middletime		= "$this->day_of_month_short. $months[$month] $year / $hour:$minute $sl_language[hour] ";
					$this->shorttime		= "$this->day_of_month_short. $months[$month] $year";
					$this->extrashorttime	= "$this->day_of_month_short.$this->monthnumber_long.<BR>$year";
					break; 
				case 'm/d/y':
					$this->longtime			= "$months[$month] $this->day_of_month_short$this->addEng  $year / $hour:$minute:$second $sl_language[hour] ";
	       			$this->middletime		= "$months[$month] $this->day_of_month_short$this->addEng $year / $hour:$minute $sl_language[hour] ";
					$this->shorttime		= "$months[$month] $this->day_of_month_short$this->addEng $year";
					$this->extrashorttime	= "$this->monthnumber_long/$this->day_of_month_short/<BR>$year";
				break;
				default :
					$this->longtime			= "$this->day_of_month_short. $months[$month] $year / $hour:$minute:$second $sl_language[hour] ";
	       			$this->middletime		= "$this->day_of_month_short. $months[$month] $year / $hour:$minute $sl_language[hour] ";
					$this->shorttime		= "$this->day_of_month_short. $months[$month] $year";
					$this->extrashorttime	= "$this->day_of_month_short.$this->monthnumber_long.<BR>$year";
					break; 
			}

		}

    } /* END timestamp_to_date */

?>