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
	//
	// only used in test
	//
		
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
	
	
	//
	// widely used
	// 
	
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