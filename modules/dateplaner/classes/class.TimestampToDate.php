<?php
/**
* Gui Class
*
* this class should manage the timestamp functions
* 
* @author Frank Grümmert 
* 
* @version $Id: class.TimestampToDate.php,v 0.9 2003/06/11 
* @package application
* @access public
*
* Diese Klassse ermöglicht das formatierten eines timestamps.   
*/


class TimestampToDate {
	
	
		/**
		* function ttd($timestamp)
		* @description : send formated timstamp strings back
		* @param int timestamp
		* @global Array CSCW_language ( iunclude Langeproperties )
		* @return object
		*/

	    function ttd($timestamp) {

			global $CSCW_language;

	    	$tdd		=  date("d:w:m:Y:H:i:s",$timestamp);

    	  	$months		= array("01"=>$CSCW_language[long_01],"02"=>$CSCW_language[long_02],"03"=>$CSCW_language[long_03],"04"=>$CSCW_language[long_04],"05"=>$CSCW_language[long_05],"06"=>$CSCW_language[long_06],"07"=>$CSCW_language[long_07],"08"=>$CSCW_language[long_08],"09"=>$CSCW_language[long_09],"10"=>$CSCW_language[long_10],"11"=>$CSCW_language[long_11],"12"=>$CSCW_language[long_12]);

        	$days		=   array("0"=>$CSCW_language[Mo_long],"1"=>$CSCW_language[Tu_long],"2"=>$CSCW_language[We_long],"3"=>$CSCW_language[Th_long],"4"=>$CSCW_language[Fr_long],"5"=>$CSCW_language[Sa_long],"6"=>$CSCW_language[Su_long]);

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

			$Date_format_middle		= $CSCW_language[date_format_middle];
			switch($Date_format_middle) {
				case 'd/m/y':
					$this->longtime			= "$this->day_of_month_short. $months[$month] $year / $hour:$minute:$second $sl_language[hour] ";
	       			$this->middletime		= "$this->day_of_month_short. $months[$month] $year / $hour:$minute $sl_language[hour] ";
					$this->shorttime		= "$this->day_of_month_short. $months[$month] $year";
					break; 
				case 'm/d/y':
					$this->longtime			= "$months[$month] $this->day_of_month_short$this->addEng  $year / $hour:$minute:$second $sl_language[hour] ";
	       			$this->middletime		= "$months[$month] $this->day_of_month_short$this->addEng $year / $hour:$minute $sl_language[hour] ";
					$this->shorttime		= "$months[$month] $this->day_of_month_short$this->addEng $year";
					break;
				default :
					$this->longtime			= "$this->day_of_month_short. $months[$month] $year / $hour:$minute:$second $sl_language[hour] ";
	       			$this->middletime		= "$this->day_of_month_short. $months[$month] $year / $hour:$minute $sl_language[hour] ";
					$this->shorttime		= "$this->day_of_month_short. $months[$month] $year";
					break; 
			}

		}

    } /* END timestamp_to_date */

?>
