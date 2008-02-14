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

include_once('./Services/Calendar/classes/iCal/class.ilICalComponent.php');
include_once('./Services/Calendar/classes/iCal/class.ilICalProperty.php');
include_once('./Services/Calendar/classes/iCal/class.ilICalParameter.php');

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

	protected $container = array();

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
			$i += ($offset - 1);

			// Parse this line
			$this->parseLine($line);
		}
	}
	
	/**
	 * get container 
	 *
	 * @access protected
	 */
	protected function getContainer()
	{
		return $this->container[count($this->container) - 1];
	}
	
	/**
	 * set container
	 *
	 * @access protected
	 * @param ilICalItem 
	 */
	protected function setContainer($a_container)
	{
		$this->container = array($a_container);
	}
	
	/**
	 * pop la
	 *
	 * @access protected
	 */
	protected function dropContainer()
	{
		return array_pop($this->container);
	}
	
	/**
	 * push container
	 *
	 * @access protected
	 * @param ilICalItem
	 */
	protected function pushContainer($a_container)
	{
		$this->container[] = $a_container;
		
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
			case 'BEGIN:VCALENDAR':
				$this->log->write(__METHOD__.': BEGIN VCALENDAR');
				$this->setContainer(new ilICalComponent('VCALENDAR'));
				break;
				
			case 'END:VCALENDAR':
				$this->log->write(__METHOD__.': END VCALENDAR');
				break;
			
			case 'BEGIN:VEVENT':
				$this->log->write(__METHOD__.': BEGIN VEVENT');
				$this->pushContainer(new ilICalComponent('VEVENT'));
				break;
			
			case 'END:VEVENT':
				$this->log->write(__METHOD__.': END VEVENT');
				
				var_dump("<pre>",$this->getContainer(),"</pre>");
				
				
				// TODO: save to ilCalEntry
				$this->dropContainer();
				break;

			case 'BEGIN:VTIMEZONE':
				$this->log->write(__METHOD__.': BEGIN VTIMEZONE');
				$container = new ilICalComponent('VTIMEZONE');
				$this->pushContainer($container);
				break;
				
			case 'END:VTIMEZONE':
				$this->log->write(__METHOD__.': END VTIMEZONE');
				
				if($tzid = $this->getContainer()->getItemsByName('TZID'))
				{
					$this->switchTZ($tzid[0]->getValue());
				} 
				$this->dropContainer();
				break;
			
			default:
				if(strpos(trim($line),'BEGIN') === 0)
				{
					$this->log->write(__METHOD__.': Do not handling line:'.$line);
					continue;
				}
				list($params,$values) = $this->splitLine($line);
				$this->storeItems($params,$values);
				break;
		}
	
	}
	
	/**
	 * store items
	 *
	 * @access protected
	 */
	protected function storeItems($a_param_part,$a_value_part)
	{
		// Check for a semicolon in param part and split it.
		
		$items = array();
		if($splitted_param = explode(';',$a_param_part))
		{
			$counter = 0;
			foreach($splitted_param as $param)
			{
				if(!$counter)
				{
					$items[$counter]['param'] = $param;
					$items[$counter]['value'] = $a_value_part; 
				}
				else
				{
					// Split by '='
					if($splitted_param_values = explode('=',$param))
					{
						$items[$counter]['param'] = $splitted_param_values[0];
						$items[$counter]['value'] = $splitted_param_values[1];
					}
				}
				++$counter;
			}
		}
		
		if(!count($items))
		{
			$this->log->write(__METHOD__.': Cannot parse parameter: '.$a_param_part.', value: '.$a_value_part);
			return false;
		}
		
		$counter = 0;
		foreach($items as $item)
		{
			if(!$counter)
			{
				// First is ical-Parameter
				$parameter = new ilICalProperty($item['param'],$item['value']);
				$this->getContainer()->addItem($parameter);
				$this->pushContainer($parameter);
			}
			else
			{
				$value = new ilICalParameter($item['param'],$item['value']);
				$this->getContainer()->addItem($value);
			}
			++$counter;
		}
		$this->dropContainer();
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