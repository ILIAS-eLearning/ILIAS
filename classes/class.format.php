<?php
/**
* Class format
* functions for converting date, time & money output to country specific formats
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Peter Gabriel <pgabriel@databay.de> 
* @version $Id$
*
* @package ilias-core
*/

/**
* format conversions
* @version $Id$
* @package application
*/
class Format
{
	function Format ()
	{
		return;
	}

	// Holt das aktuelle Datum und gibt es im Format TT.MM.JJJJ zurck
	function getDateDE ()
	{
		$date = getdate();
		$datum = sprintf("%02d.%02d.%04d", $date["mday"],$date["mon"],$date["year"]);
		return $datum;
	}

	/**
	* Prft eingegebes Datum und wandelt es in DB-konformen Syntax um
	* Eingabe: TT.MM.JJJJ oder T.M.JJ oder TT.MM.JJJJ HH:MM:SS oder T.M.JJ HH:MM:SS
	* Bei zweistelliger Jahresangabe wird bei YY > 70 19, bei YY < 70 20 vorgestellt
	* Ausgabe: YYYY-MM-DD oder YYYY-MM-DD HH:MM:SS
	* OPTIONAL wird die aktuelle Systemzeit hinzugefgt (Ausgabe: YYYY-MM-DD hh:mm:ss)
	* @param string
	*/
	function input2date ($AInputDate)
	{
		$date=""; $y=""; $m=""; $d="";

		if (ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{2,4})",$idate,$p))
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
            
            	// Uhrzeit auf Gltigkeit prfen
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
					// Uhrzeit ist falsch/fehlt; h„nge aktuelle Zeit an
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
	* formats timestamp to db-datetime
	* @param string
	*/
	function ftimestamp2dateDB ($t)
	{
		return sprintf("%04d-%02d-%02d",substr($t, 0, 4),substr($t, 4, 2),substr($t, 6, 2));
	}


	/**
	* Datum vergleichen
	* Erwartet timestamps
	* Liefert das aktuellere Datum als Timestamp zurck
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
	* Prft Zahlen mit Nachkommastellen und erlaubt ein Komma als Nachstellentrenner
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
	* formatiert Prozentzahlen (Format: 00,00%). Wenn nix oder eine Null bergeben wird, wird ein Leerzeichen zurckgegeben
	* Wenn mehr als ein Parameter bergeben wird, wird die Ausgabe auch bei Wert Null erzwungen
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
	* in different languages, dates are formatted different. 
	* formatDate reads a value "lang_dateformat" from the languagefile.
	* if it is not present it sets it to a defaultvalue given in the class
	* the format must have DD, MM, and YYYY strings
	* formatDate replaces the strings with the current values given in str
	* @access	public
	* @param	string	date date, given in sql-format YYYY-MM-DD
	* @return	string	formatted date
	*/
	function fmtDate($a_str,$a_dateformat)
	{
		//read the format
		$date = $a_dateformat;

		//no format defined set to defaultformat
		if ($a_dateformat == "-lang_dateformat-")
		{
			$date = "MM/DD/YYYY";
		}

		//get values from given sql-date
		$d = substr($a_str,8,2);
		$m = substr($a_str,5,2);
		$y = substr($a_str,0,4);
		
		//do substitutions
		$date = ereg_replace("DD", $d, $date);
		$date = ereg_replace("MM", $m, $date);
		$date = ereg_replace("YYYY", $y, $date);

		return $date;
	}

	/** 
	* formatting function for datetime
	* @access	public
	* @param	string	datetime given in sql-format YYYY-MM-DD HH:MM:SS
	* @param	string	format type (normal is as given in lang_dateformat)
	* @return	string	formatted date
	* @see		fmtDate()
	*/
	function fmtDateTime($a_str, $a_fmt="normal")
	{
		//formate date-part
		$datetime = $this->fmtDate($a_str, $a_fmt);

		//format timeformat
		$datetime .= " ".substr($a_str,11,2).":".substr($a_str,14,2);
		
		return $datetime;
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
			if ($a_th == "-sep_thousand-")
			{
				$a_th = ",";
			}
		}
		
		//decimalpoint?
		$dec = $a_decimals;
		
		if ($dec == "-sep_decimal-")
		{
			$dec = ".";
		}

		return number_format($a_float, $a_decimals, $dec, $a_th);
	}	
}
?>