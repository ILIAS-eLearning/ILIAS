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

include_once('./Services/Calendar/classes/iCal/class.ilICalUtils.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
include_once('./Services/Calendar/classes/class.ilTimeZone.php');
include_once('./Services/Calendar/classes/class.ilTimeZoneException.php');

/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup Services/Calendar
*/
class ilICalParser
{
	const INPUT_STRING = 1;
	const INPUT_FILE = 2;
	
	protected $log = null;

	protected $ical = '';
	protected $file = '';
	protected $timezone = null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string ical string
	 * 
	 */
	public function __construct($a_ical,$a_type)
	{
		global $ilLog;
		
		if($a_type == self::INPUT_STRING)
		{
		 	$this->ical = $a_ical;
		}
		elseif($a_type == self::INPUT_FILE)
		{
			$this->file = $a_ical;
			$this->ical = file_get_contents($a_ical);
		}
	 	$this->log = $ilLog;
	}
	
	/**
	 * Parse input
	 *
	 * @access public
	 * 
	 */
	public function parse()
	{
		include_once('./Services/Calendar/lib/parse_ics.php');
		
		$this->timezone = ilTimeZone::_getInstance();
		
		$lines = $this->tokenize($this->ical,ilICalUtils::ICAL_EOL);
	 	for($i = 0; $i < count($lines); $i++)
		{
			$line = $lines[$i];

			// Check for next multilines (they start with a space)
			$offset = 1;
			while(isset($lines[$i + $offset]) and strpos($lines[$i + $offset],' ') === 0)
			{
				$lines[$i + $offset] = str_replace(ilICalUtils::ICAL_EOL,'',$lines[$i + $offset]);
				$line = $line.substr($lines[$i + $offset],1);
				$offset++;
			}
			$i += $offset - 1;

			// Parse this line
			$this->parseLine($line);
		}
	}
	
	/**
	 * parse a line
	 *
	 * @access protected
	 */
	protected function parseLine($line)
	{
		switch(trim($line))
		{
			case 'BEGIN:VEVENT':
				#$this->log->write(__METHOD__.': BEGIN VEVENT');
			
				// start new vevent
				$this->current_obj = new ilCalendarEntry();
				break;
			
			case 'BEGIN:VTIMEZONE':
				$this->log->write(__METHOD__.': BEGIN VTIMEZONE');
				break;
			
			
			default:
				list($param,$values) = $this->splitLine($line);
				$this->parseParameters($param,$values);
				#$this->log->write(__METHOD__.': Found param: '.$param);
				break;
									
		}
	
	}
	
	/**
	 * parse parameters
	 *
	 * @access protected
	 */
	protected function parseParameters($a_param,$a_values)
	{
		
		// TODO: split semicolon seperated parameters
		switch($a_param)
		{
			case 'TZID':
				$this->log->write(__METHOD__.': Found TZID => '.$a_values);
				$this->switchTZ($a_values);
				break;
		}
	}
	
	/**
	 * parse parameters
	 *
	 * @access protected
	 * @param string a line
	 */
	protected function splitLine($a_line)
	{
		$matches = array();
		
		if(preg_match('/([^:]+):(.*)/',$a_line,$matches))
		{
			return array($matches[1],$matches[2]);
		}
		else
		{
			$this->log->write(__METHOD__.': Found invalid parameter: '.$a_line);
		}
		return array('','');
	}
	
	/**
	 * tokenize string
	 *
	 * @access protected
	 */
	protected function tokenize($a_string,$a_tokenizer)
	{
		return explode($a_tokenizer,$a_string); 
	}
	
	/**
	 * Switch timezone
	 *
	 * @access protected
	 */
	protected function switchTZ($a_timezone)
	{
		$parts = explode('/',$a_timezone);
		$tz = array_pop($parts);
		$continent = array_pop($parts);
		
		if(isset($continent) and $continent)
		{
			$timezone = $continent.'/'.$tz;
		}
		else
		{
			$timezone = $a_timezone;
		}
		
		try
		{
			if($this->timezone->getIdentifier() == $timezone)
			{
				return true;
			}
			else
			{
				$this->log->write(__METHOD__.': Switched to timezone: '.$timezone);
				$this->timezone = ilTimeZone::_getInstance($timezone);
			}
		}
		catch(ilTimeZoneException $e)
		{
			$this->log->write(__METHOD__.': Found invalid timezone: '.$timezone);
			return false;
		}		
	}
}


?>