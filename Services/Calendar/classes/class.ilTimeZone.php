<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* This class offers methods for timezone handling.
* <code>ilTimeZone::_getDefault</code> tries to "guess" the server timezone in the following manner:
* 1) PHP >= 5.2.0 use <code>date_default_timezone_get</code>
* 2) Read ini option date.timezone if available
* 3) Read environment PHP_TZ
* 4) Read environment TZ
* 5) Use <code>date('T')</code>
* 6) Use UTC
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

include_once('Services/Calendar/classes/class.ilTimeZoneException.php');

class ilTimeZone
{
	const UTC = 'UTC';
	
	protected static $default_timezone = '';
	protected static $current_timezone = '';
	
	protected $timezone = "UTC";

	/**
	 * Create new timezone object
	 * If no timezone is given, the default server timezone is chosen. 
	 *
	 * @access public
	 * @param string valid timezone
	 * 
	 */
	public function __construct($a_timezone = '')
	{
		if($a_timezone)
		{
			$this->timezone = $a_timezone;
		}
		else
		{
			$this->timezone = self::_getDefaultTimeZone();
		}
		if(!self::$default_timezone)
		{
			self::_getDefaultTimeZone();
		}
	}
	
	/**
	 * Switch timezone to given timezone
	 *
	 * @access public
	 */
	public function switchTZ()
	{
	 	self::_switchTimeZone($this->timezone);
	}
	
	/**
	 * Restore default timezone
	 *
	 * @access public
	 * @throws ilTimeZoneException
	 */
	public function restoreTZ()
	{
	 	try
	 	{
	 		self::_switchTimeZone(self::$default_timezone);
	 	}
	 	catch(ilTimeZoneExxception $e)
	 	 {
	 	 	throw $e;
	 	 }
	}
	
	/**
	 * Switch tz  
	 *
	 * @access public
	 * @static
	 * @throws ilTimeZoneException
	 */
	public static function _switchTimeZone($a_timezone)
	{
		global $ilLog;
		
		if(self::$current_timezone == $a_timezone)
		{
			return true;
		}
		
		// PHP >= 5.2.0
		if(function_exists('date_default_timezone_set'))
		{
			if(!date_default_timezone_set($a_timezone))
			{
				$ilLog->write(__METHOD__.': Invalid timezone given. Timezone: '.$a_timezone);
				throw new ilTimeZoneException('Invalid timezone given'); 
			}
		}
		if(!putenv('TZ='.$a_timezone))
		{
			$ilLog->write(__METHOD__.': Cannot set TZ environment variable. Please register TZ in php.ini (safe_mode_allowed_env_vars). Timezone');
			throw new ilTimeZoneException('Cannot set TZ environment variable.'); 
		}
		return true;
	}
	
	
	/**
	 * Calculate and set default time zone
	 *
	 * @access public
	 * @static
	 * @return time zone string
	 */
	public static function _getDefaultTimeZone()
	{
		if(strlen(self::$default_timezone))
		{
			return self::$default_timezone;
		}
		// PHP >= 5.2.0
		if(function_exists('date_default_timezone_get') and $tz = date_default_timezone_get())
		{
			return self::$default_timezone = $tz;
		}
		// PHP ini option (PHP >= 5.1.0)
		if($tz = ini_get('date.timezone'))
		{
			return self::$default_timezone = $tz;
		}
		// is $_ENV['PHP_TZ'] set ?
		if($tz = getenv('PHP_TZ'))
		{
			return self::$default_timezone = $tz;
		}
		// is $_ENV['TZ'] set ?
		if($tz = getenv('TZ'))
		{
			return self::$default_timezone = $tz;
		}
		if(strlen($tz = date('T')))
		{
			return self::$default_timezone = $tz;
		}
		return self::$default_timezone = self::UTC;
	}
}
?>