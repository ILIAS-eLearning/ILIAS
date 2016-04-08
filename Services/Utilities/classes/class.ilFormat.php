<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class format
* functions for converting date, time & money output to country specific formats
* 
* DEPRECATED use ilDate ilDateTime and ilDatePresentation.
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Peter Gabriel <pgabriel@databay.de> 
* @version $Id$
* @deprecated since version 3.10 - 05.03.2009
*
*/

/**
* format conversions
* @package application
*/
class ilFormat
{
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
	public static function dateDB2timestamp ($ADatumSQL)
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
	public static function ftimestamp2datetimeDB($aTimestamp)
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
	* @param	boolean	relative date output
	* @return	string	formatted date
	* @deprecated since 3.10 - 05.03.2009
	*/
	public static function fmtDateTime($a_str,$a_dateformat,$a_timeformat,$a_mode = "datetime", $a_relative = TRUE)
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

		// The sql-date 0000-00-00 00:00:00 means "no-date given"
		if ($a_str == '0000-00-00 00:00:00') 
		{
			global $lng;
			return $lng->txt('no_date');
		}
                //
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
		if ($a_relative)
		{
			$date = ($isToday) ? $lng->txt('today') : 
					(($isYesterday) ? $lng->txt('yesterday') : 
					(($isTomorrow) ? $lng->txt('tomorrow') : 
					date($a_dateformat,mktime($h,$i,$s,$m,$d,$y))))
					;
		}
		else
		{
			$date = date($a_dateformat,mktime($h,$i,$s,$m,$d,$y));
		}
				
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
	* @param	boolean		whether .0 should be suppressed
	* @return	string		formatted number
	*/
	static function fmtFloat($a_float, $a_decimals=0, $a_dec_point = null, $a_thousands_sep = null, $a_suppress_dot_zero=false)
	{
		global $lng;


		if ($a_dec_point == null)
		{
			$a_dec_point = $lng->txt('lang_sep_decimal');
			{
				$a_dec_point = ".";
			}
		}
		if ($a_dec_point == '-lang_sep_decimal-')
		{
			$a_dec_point = ".";
		}

		if ($a_thousands_sep == null)
		{
			$a_thousands_sep = $lng->txt('lang_sep_thousand');
			{
				$a_th = ",";
			}
		}
		if ($a_thousands_sep == '-lang_sep_thousand-')
		{
			$a_thousands_sep = ",";
		}
		
		$txt = number_format($a_float, $a_decimals, $a_dec_point, $a_thousands_sep);
		
		// remove trailing ".0" 
		if (($a_suppress_dot_zero == 0 || $a_decimal == 0) &&
			substr($txt,-2) == $a_dec_point.'0')
		{
			$txt = substr($txt, 0, strlen($txt) - 2);
		}
		if ($a_float == 0 and $txt == "")
		{
			$txt = "0";
		}
		return $txt;
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
	
	/**
	* format a date according to the user language 
	* shortcut for Format::fmtDateTime
	* @access	public
	* @param	string	sql date
	* @param	string	format mode
	* @param boolean Relative date output
	* @return	string	formatted date
	* @see		Format::fmtDateTime
	* @deprecated since 3.10 - 05.03.2009
	*/
	public static function formatDate($a_date,$a_mode = "datetime", $a_omit_seconds = false, $a_relative = TRUE)
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

		return ilFormat::fmtDateTime($a_date,$dateformat,$timeformat,$a_mode, $a_relative);
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
	 * @param int $seconds seconds
	 * @param bool $force_with_seconds
	 * @param ilLanguage $a_lng
	 * @return string
	 */
	public static function _secondsToString($seconds, $force_with_seconds = false, $a_lng = null)
	{
		global $lng;

		if($a_lng)
		{
			$lng = $a_lng;
		}

		$seconds = $seconds ? $seconds : 0;
		
		// #13625
		if($seconds > 0)
		{
			$days = floor($seconds / 86400);
			$rest = $seconds % 86400;

			$hours = floor($rest / 3600);
			$rest = $rest % 3600;

			$minutes = floor($rest / 60);
			$seconds = $rest % 60;
		}
		else
		{
			$days = ceil($seconds / 86400);
			$rest = $seconds % 86400;

			$hours = ceil($rest / 3600);
			$rest = $rest % 3600;

			$minutes = ceil($rest / 60);
			$seconds = $rest % 60;
		}

		if($days)
		{
			$message = $days . ' '. ($days == 1 ? $lng->txt('day') : $lng->txt('days'));
		}
		if($hours)
		{
			if($message)
			{
				$message .= ' ';
			}
			$message .= ($hours . ' '. ($hours == 1 ? $lng->txt('hour') : $lng->txt('hours')));
		}
		if($minutes)
		{
			if($message)
			{
				$message .= ' ';
			}
			$message .= ($minutes . ' '. ($minutes == 1 ? $lng->txt('minute') : $lng->txt('minutes')));
		}
		if($force_with_seconds && $seconds)
		{
			if($message)
			{
				$message .= ' ';
			}
			$message .= ($seconds . ' '. ($seconds == 1 ? $lng->txt('second') : $lng->txt('seconds')));
		}
		if(!$days and !$hours and !$minutes)
		{
			return $seconds .' '. ($seconds == 1 ? $lng->txt('second') : $lng->txt('seconds'));
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
	public static function _secondsToShortString($seconds)
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

	/**
	 * converts a float number to money format, 
	 * depending on system language 
	 * 
	 **/	 
	static function _getLocalMoneyFormat($float_number)
	{
		global $ilias;
		
		$language = $ilias->getSetting("language");
		$money_locale = $language.'_'.strtoupper($language);
		/* de_DE en_US en_EN fr_FR .UTF-8
		*/ //$money_locale = 'de_DE.UTF-8';
		//vd($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
		setlocale(LC_MONETARY, $money_locale);
		return $float_number;
		//return money_format('%!2n', $float_number);
	}

	/**
	 * Returns the magnitude used for size units.
	 *
	 * This function always returns the value 1024. Thus the value returned
	 * by this function is the same value that Windows and Mac OS X return for a
	 * file. The value is a GibiBit, MebiBit, KibiBit or byte unit.
	 *
	 * For more information about these units see:
	 * http://en.wikipedia.org/wiki/Megabyte
	 *
	 * @return <type>
	 */
	public static function _getSizeMagnitude()
	{
		return 1024;
	}
	/**
	 * Returns the specified float in human friendly form.
	 * <p>
	 *
	 * @param	float	a float
	 * @param	ilLanguage  The language object, or null if you want to use the system language.
	 */
	public static function formatFloat($size, $a_decimals, $a_suppress_dot_zero=false, $a_mode = 'short', $a_lng = null)
	{
		global $lng;
		if ($a_lng == null) {
			$a_lng = $lng;
		}
		return self::fmtFloat($size, $a_decimals, $a_lng->txt('lang_sep_decimal'), $a_lng->txt('lang_sep_thousand', $a_suppress_dot_zero), true).' '.$a_lng->txt($scaled_unit);
	}
	/**
	 * Returns the specified file size value in a human friendly form.
	 * <p>
	 * By default, the oder of magnitude 1024 is used. Thus the value returned
	 * by this function is the same value that Windows and Mac OS X return for a
	 * file. The value is a GibiBig, MebiBit, KibiBit or byte unit.
	 * <p>
	 * For more information about these units see:
	 * http://en.wikipedia.org/wiki/Megabyte
	 *
	 * @param	integer	size in bytes
	 * @param	string	mode:
	 *                  "short" is useful for display in the repository
	 *                  "long" is useful for display on the info page of an object
	 * @param	ilLanguage  The language object, or null if you want to use the system language.
	 */
	public static function formatSize($size, $a_mode = 'short', $a_lng = null)
	{
		global $lng;
		if ($a_lng == null) {
			$a_lng = $lng;
		}

		$result;
		$mag = self::_getSizeMagnitude();

		$scaled_size;
		$scaled_unit;

		if ($size >= $mag * $mag * $mag)
		{
			$scaled_size = $size/$mag/$mag/$mag;
			$scaled_unit = 'lang_size_gb';
		}
		else if ($size >= $mag * $mag)
		{
			$scaled_size = $size/$mag/$mag;
			$scaled_unit = 'lang_size_mb';
		}
		else if ($size >= $mag)
		{
			$scaled_size = $size/$mag;
			$scaled_unit = 'lang_size_kb';
		}
		else
		{
			$scaled_size = $size;
			$scaled_unit = 'lang_size_bytes';
		}

		$result = self::fmtFloat($scaled_size,($scaled_unit == 'lang_size_bytes') ? 0:1, $a_lng->txt('lang_sep_decimal'), $a_lng->txt('lang_sep_thousand'), true).' '.$a_lng->txt($scaled_unit);
		if ($a_mode == 'long' && $size > $mag)
		{
			$result .= ' ('.
				self::fmtFloat($size,0,$a_lng->txt('lang_sep_decimal'),$a_lng->txt('lang_sep_thousand')).
				' '.$a_lng->txt('lang_size_bytes').')';
		}
		return $result;
	}
}

?>
