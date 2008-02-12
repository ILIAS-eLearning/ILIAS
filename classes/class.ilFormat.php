<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class format
* functions for converting date, time & money output to country specific formats
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Peter Gabriel <pgabriel@databay.de> 
* @version $Id$
*
*/

/**
* format conversions
* @package application
*/
class ilFormat
{
	function ilFormat ()
	{
		return;
	}

	// Holt das aktuelle Datum und gibt es im Format TT.MM.JJJJ zurck
	function getDateDE ()
	{
		$date = getdate();
		$datum = sprintf("%02d.%02d.%04d", $date["mday"],$date["mon"],$date["year"]);
		return $datum;
	}

	/**
	* Prft eingegebes Datum und wandelt es in DB-konformen Syntax um
	* Eingabe: TT.MM.JJJJ oder T.M.JJ oder TT.MM.JJJJ HH:MM:SS oder T.M.JJ HH:MM:SS
	* Bei zweistelliger Jahresangabe wird bei YY > 70 19, bei YY < 70 20 vorgestellt
	* Ausgabe: YYYY-MM-DD oder YYYY-MM-DD HH:MM:SS
	* OPTIONAL wird die aktuelle Systemzeit hinzugefgt (Ausgabe: YYYY-MM-DD hh:mm:ss)
	* @param string
	*/
	function input2date ($AInputDate)
	{

		$date=""; $y=""; $m=""; $d="";
//		if (ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})",$idate,$p))
		if (ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})",$AInputDate,$p))
		{
            	
			$d = $p[1];
			$m = $p[2];
			$y = $p[3];
			
			if (($d>0 && $d<32) && ($m>0 && $m<13) && (strlen($y)!=3))
			{
				if (strlen($d) == 1) $d = "0".$d;
				if (strlen($m) == 1) $m = "0".$m;

				if (strlen($y) == 2)
				{
					if ($y>=70) $y = $y + 1900;
					if ($y<70) $y = $y + 2000;
				}
				
				// is valid?
				checkdate($m, $d, $y);

				// Ausgabe mit Uhrzeit

            	// Uhrzeit holen
            	$uhrzeit = substr($AInputDate, -8);

            	// Uhrzeit auf Gltigkeit prfen
            	if (ereg("([0-9]{2}):([0-9]{2}):([0-9]{2})",$AInputDate,$p))
            	{
					$h   = $p[1];
					$min = $p[2];
					$s   = $p[3];
					
					if (($h>-1 && $h<24) && ($min>-1 && $min<60) && ($s>-1 && $s<60))
					{
						// Uhrzeit stimmt/ist vorhanden
						$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$y,$m,$d,$h,$min,$s);
					}
				}
				else
				{
					// Uhrzeit ist falsch/fehlt; hnge aktuelle Zeit an
					$zeit = getdate();
					$date = sprintf("%04d-%02d-%02d %02d:%02d:%02d",$y,$m,$d,$zeit["hours"],$zeit["minutes"],$zeit["seconds"]);
				}
				// Ausgabe ohne Uhrzeit
				//$date = sprintf("%04d-%02d-%02d",$y,$m,$d);
				return $date;
			}
		}
	}
	

	/**
	* db-datetime to timestamp
	* @param string
	*/
	function dateDB2timestamp ($ADatumSQL)
	{
		$timestamp = substr($ADatumSQL, 0, 4).
					 substr($ADatumSQL, 5, 2).
					 substr($ADatumSQL, 8, 2).
					 substr($ADatumSQL, 11, 2).
					 substr($ADatumSQL, 14, 2).
					 substr($ADatumSQL, 17, 2);

		return $timestamp;
	}

    /**
	* German datetime to timestamp
	* @param string
	*/
	function dateDE2timestamp ($ADatum)
	{
		$timestamp = substr($ADatum, 6, 4).
					 substr($ADatum, 3, 2).
					 substr($ADatum, 0, 2).
					 substr($ADatum, 11, 2).
					 substr($ADatum, 14, 2).
					 substr($ADatum, 17, 2);
					 
		return $timestamp;
	}


	/**
	* formats db-datetime to german date
	* @param string
	*/
	function fdateDB2dateDE ($t)
	{
		return sprintf("%02d.%02d.%04d",substr($t, 8, 2),substr($t, 5, 2),substr($t, 0, 4));
	}


	/**
	* formats timestamp to german date
	* @param string
	*/
	function ftimestamp2dateDE ($t)
	{
		return sprintf("%02d.%02d.%04d",substr($t, 6, 2),substr($t, 4, 2),substr($t, 0, 4));
	}


	/**
	* formats timestamp to german datetime
	* @param string
	*/
	function ftimestamp2datetimeDE ($t)
	{
		return sprintf("%02d.%02d.%04d %02d:%02d:%02d",substr($t, 6, 2),substr($t, 4, 2),substr($t, 0, 4),substr($t, 8, 2),substr($t, 10, 2),substr($t, 12, 2));
	}


	/**
	* formats timestamp to db-date
	* @param string
	*/
	function ftimestamp2dateDB ($t)
	{
		return sprintf("%04d-%02d-%02d",substr($t, 0, 4),substr($t, 4, 2),substr($t, 6, 2));
	}

    /**
	* Timestamp to database datetime
	*
	* @param string $aTimestamp String in timestamp format
	* @return string Database datetime in format yyyy-mm-dd hh:mm:ss
	*/
	function ftimestamp2datetimeDB($aTimestamp)
	{
		$date = "";
		if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $aTimestamp, $matches))
		{
			$date = "$matches[1]-$matches[2]-$matches[3] $matches[4]:$matches[5]:$matches[6]";
		}
		return $date;
	}


	/**
	* Datum vergleichen
	* Erwartet timestamps
	* Liefert das aktuellere Datum als Timestamp zurck
	* @param string
	* @param string
	*/
	function compareDates ($ADate1,$ADate2)
	{
		if ($ADate1 > $ADate2)
		{
			return $ADate1;
		}
		
		return $ADate2;
	}
	

	/**
	* Prft Zahlen mit Nachkommastellen und erlaubt ein Komma als Nachstellentrenner
	* @param string
	*/
	function checkDecimal ($var)
	{
		return doubleval(ereg_replace (",",".",$var));
	}


	/**
	* formatiert Geldwerte (Format: 00,00 + Eurosymbol). Weiteres siehe fProzent
	*/
	function fGeld ()
	{
		$num_args = func_num_args();
		
		$geld = func_get_arg(0);
		
		if ($num_args == 1)
		{
			$test = intval($geld);
			
			if (!$test)
				return "&nbsp;";
		}
		
		return number_format($geld,2,",",".")." &euro;";
	}


	/**
	* formatiert Prozentzahlen (Format: 00,00%). Wenn nix oder eine Null bergeben wird, wird ein Leerzeichen zurckgegeben
	* Wenn mehr als ein Parameter bergeben wird, wird die Ausgabe auch bei Wert Null erzwungen
	*/
	function fProzent ()
	{
		$num_args = func_num_args();
		
		$prozent = func_get_arg(0);

		if ($num_args == 1)
		{
			$test = intval($prozent);
			
			if (!$test)
				return "&nbsp;";
		}
		
		return number_format($prozent,2,",",".")."%";
	}
	
	/**
	* Floats auf 2 Nachkommastellen runden 
	* @param float
	*/
	function runden ($value)
	{
		return round($value * 100) / 100;
	}

	/** 
	* formatting function for dates
	*
	* In different languages, dates are formatted different. 
	* fmtDate expects a sql timestamp and a date format.
	* Optional you may specify a time format. If you skip this parameter no time is displayed
	* The format options follows the rules of the PHP date-function. See in the PHP manual
	* for a list of possible formatting options
	* @access	public
	* @param	string	date date, given in sql-format YYYY-MM-DD HH:MM:SS
	* @param	string	date format (default is Y-m-d)
	* @param	string	time format (default is H:i:s)
	* @param	string	format mode (datetime, time or date)
	* @return	string	formatted date
	*/
	function fmtDateTime($a_str,$a_dateformat,$a_timeformat,$a_mode = "datetime")
	{
		//no format defined. set to default format
		if ($a_dateformat == "")
		{
			$a_dateformat = "Y-m-d";
		}
		
		// same for time format
		if ($a_timeformat == "")
		{
			$a_timeformat = "H:i:s";
		}

		//get values from given sql-date
		$d = substr($a_str,8,2);
		$m = substr($a_str,5,2);
		$y = substr($a_str,0,4);
		$h = substr($a_str,11,2);
		$i = substr($a_str,14,2);
		$s = substr($a_str,17,4);

		// Minimum date is 1.1.1970
		if(($y < 1970) or
		   ($y == 1970 and ($m < 1 or $d < 1)))
		{
			$y = 1970;
			$m = 1;
			$d = 2;
			$h = $i = $s = 0;
		}

		if ($a_mode == "time")
		{
			return date($a_timeformat,mktime($h,$i,$s,1,1,1999));		
		}
		
		// BEGIN WebDAV: Display relative date.
		$timestamp = mktime($h,$i,$s,$m,$d,$y);
		$now = time();
		$minuteswest = gettimeofday(false);
		$minuteswest = $minuteswest['minuteswest'];
		$today = $now - $now % (24 * 60 * 60) + $minuteswest * 60;
		$isToday = $today <= $timestamp && $timestamp < $today + 24 * 60 * 60;
		$isYesterday = $today - 24 * 60 * 60 <= $timestamp && $timestamp < $today;
		$isTomorrow = $today + 24 * 60 * 60 <= $timestamp && $timestamp < $today + 48 * 60 * 60;

		global $lng;
		$date = ($isToday) ? $lng->txt('today') : 
				(($isYesterday) ? $lng->txt('yesterday') : 
				(($isTomorrow) ? $lng->txt('tomorrow') : 
				date($a_dateformat,mktime($h,$i,$s,$m,$d,$y))))
				;
				
		return ($a_mode == "date") ? $date : $date.' '.date($a_timeformat,mktime($h,$i,$s,$m,$d,$y));
		// END WebDAV: Display relative date.
	}
	
	/**
	* format a float
	* 
	* this functions takes php's number_format function and 
	* formats the given value with appropriate thousand and decimal
	* separator.
	* @access	public
	* @param	float		the float to format
	* @param	integer		count of decimals
	* @param	integer		display thousands separator
	* @return	string		formatted number
	*/
	function fmtFloat($a_float, $a_decimals = "", $a_th = "")
	{
		//thousandskomma?
		if (!empty($a_th))
		{
			if ($a_th == "-lang_sep_thousand-")
			{
				$a_th = ",";
			}
		}
		
		//decimalpoint?
		$dec = $a_decimals;
		
		if ($dec == "-lang_sep_decimal-")
		{
			$dec = ".";
		}

		return number_format($a_float, $a_decimals, $dec, $a_th);
	}

	function unixtimestamp2datetime($a_unix_timestamp = "")
	{
		if (strlen($a_unix_timestamp) == 0)
		{
			return strftime("%Y-%m-%d %H:%M:%S");
		}
		else
		{
			return strftime("%Y-%m-%d %H:%M:%S", $a_unix_timestamp);
		}
	}
	
	/*
	* format a date according to the user language 
	* shortcut for Format::fmtDateTime
	* @access	public
	* @param	string	sql date
	* @param	string	format mode
	* @return	string	formatted date
	* @see		Format::fmtDateTime
	*/
	function formatDate($a_date,$a_mode = "datetime", $a_omit_seconds = false)
	{
		global $lng;
		
		// return when no datetime is given
		if ($a_date == "0000-00-00 00:00:00")
		{
			return $lng->txt("no_date");
		}

		$dateformat = $lng->txt("lang_dateformat");
		if ($a_omit_seconds && ($lng->txt("lang_timeformat_no_sec") != "-lang_timeformat_no_sec-"))
		{
			$timeformat = $lng->txt("lang_timeformat_no_sec");
		}
		else
		{
			$timeformat = $lng->txt("lang_timeformat");
		}
		
		if ($dateformat == "-lang_dateformat-")
		{
			$dateformat = "";
		}
		
		if ($timeformat == "-lang_timeformat-")
		{
			$timeformat = "";
		}

		return ilFormat::fmtDateTime($a_date,$dateformat,$timeformat,$a_mode);
	}

	function formatUnixTime($ut,$with_time = false)
	{
		global $lng;

		$format = $lng->txt('lang_dateformat');

		if($with_time)
		{
			$format .= (' '.$lng->txt('lang_timeformat_no_sec'));
		}
		return date($format,$ut);
	}
	/*
	* calculates the difference between 2 unix timestamps and
	* returns a proper formatted output
	* 
	* @param	integer	unix timestamp1
	* @param	integer	unix timestamp2
	* @return	string	time difference in hh:mm:ss
	*/
	function dateDiff($a_ts1,$a_ts2)
	{
		$r = $a_ts2 - $a_ts1;
		
		$dd = floor($r/86400);

		if ($dd <= 9)
			$dd = "0".$dd;

		$r = $r % 86400;
		
		$hh = floor($r/3600);
		
		if ($hh <= 9)
			$hh = "0".$hh;
		
		$r = $r % 3600;
		
		$mm = floor($r/60) ;
		
		if ($mm <= 9)
			$mm = "0".$mm;
		
		$r = $r % 60;
		$ss = $r;

		if ($ss <= 9)
			$ss = "0".$ss;

		return $hh.":".$mm.":".$ss;
	}

	/**
	* converts datetime to a unix timestamp
	*
	* @param	string	datetime
	* @return	integer	unix timestamp  
	*/
	function datetime2unixTS($a_datetime)
	{
		$arrDT = explode(" ", $a_datetime);
		$arrD = explode("-", $arrDT[0]);
		$arrT = explode(":", $arrDT[1]);

		return mktime($arrT[0], $arrT[1], $arrT[2], $arrD[1], $arrD[2], $arrD[0]);
	}

	/**
	* converts seconds to string:
	* Long: 7 days 4 hour(s) ...
	*
	* @param	string	datetime
	* @return	integer	unix timestamp  
	*/
	function _secondsToString($seconds)
	{
		global $lng;

		$seconds = $seconds ? $seconds : 0;

		global $lng;

		$days = floor($seconds / 86400);
		$rest = $seconds % 86400;

		$hours = floor($rest / 3600);
		$rest = $rest % 3600;

		$minutes = floor($rest / 60);

		if($days)
		{
			$message = $days . ' '. $lng->txt('days');
		}
		if($hours)
		{
			if($message)
			{
				$message .= ' ';
			}
			$message .= ($hours . ' '. $lng->txt('hours'));
		}
		if($minutes)
		{
			if($message)
			{
				$message .= ' ';
			}
			$message .= ($minutes . ' '. $lng->txt('minutes'));
		}
		if(!$days and !$hours and !$minutes)
		{
			return $seconds .' '. $lng->txt('seconds');
		}
		else
		{
			return $message;
		}
	}
	/**
	* converts seconds to string:
	* Long: 7 days 4 hour(s) ...
	*
	* @param	string	datetime
	* @return	integer	unix timestamp  
	*/
	function _secondsToShortString($seconds)
	{
		global $lng;

		$seconds = $seconds ? $seconds : 0;

		global $lng;

		$days = floor($seconds / 86400);
		$rest = $seconds % 86400;

		$hours = floor($rest / 3600);
		$rest = $rest % 3600;

		$minutes = floor($rest / 60);
		$rest = $rest % 60;

		return sprintf("%02d:%02d:%02d:%02d",$days,$hours,$minutes,$rest);

	}
}
?>
