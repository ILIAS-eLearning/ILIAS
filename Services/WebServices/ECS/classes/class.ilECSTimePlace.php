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
 *  Representation of ECS EContent Time Place
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 *  
 * @ingroup ServicesWebServicesECS 
 */
class ilECSTimePlace
{
	public $room = '';
	public $begin = '';
	public $end = '';
	public $cycle = '';

	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct()
	{
		
	}

	/**
	 * load from json
	 *
	 * @access public
	 * @param object json representation
	 * @throws ilException
	 */
	public function loadFromJson($a_json)
	{
		global $ilLog;

		if(!is_object($a_json))
		{
			$ilLog->write(__METHOD__ . ': Cannot load from JSON. No object given.');
			throw new ilException('Cannot parse ECSContent.');
		}
		
		$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($a_json,true));

		$this->room = $a_json->room;
		$this->begin = $a_json->begin;
		$this->end = $a_json->end;
		$this->cycle = $a_json->cycle;
		#$this->day = $a_json->day;
		
		$two = new ilDate('2000-01-02',IL_CAL_DATE);
		if(ilDate::_before(new ilDateTime($this->getUTBegin(),IL_CAL_UNIX), $two))
		{
			$this->begin = '';
		}
		if(ilDate::_before(new ilDateTime($this->getUTEnd(),IL_CAL_UNIX), $two))
		{
			$this->end = '';
		}
	}

	/**
	 * set begin
	 *
	 * @access public
	 * 
	 */
	public function setBegin($a_begin)
	{
		// is it unix time ?
		if(is_numeric($a_begin) and $a_begin)
		{
			$dt = new ilDateTime($a_begin, IL_CAL_UNIX, ilTimeZone::UTC);
			$this->end = $dt->get(IL_CAL_DATE);
		}
		else
		{
			$this->begin = $a_begin;
		}
	}

	/**
	 * get begin
	 *
	 * @access public
	 */
	public function getBegin()
	{
		return $this->begin;
	}

	/**
	 * get begin as unix time
	 *
	 * @access public
	 * 
	 */
	public function getUTBegin()
	{
		include_once './Services/Calendar/classes/class.ilDateTime.php';
		$dt = new ilDateTime($this->begin, IL_CAL_DATE, ilTimeZone::UTC);
		return $dt->get(IL_CAL_UNIX);
		
	}

	/**
	 * set end
	 *
	 * @access public
	 * @param string end
	 * 
	 */
	public function setEnd($a_end)
	{
		// is it unix time ?
		if(is_numeric($a_end) and $a_end)
		{
			$dt = new ilDateTime($a_end, IL_CAL_UNIX, ilTimeZone::UTC);
			$this->end = $dt->get(IL_CAL_DATE);
		}
		else
		{
			$this->end = $a_end;
		}
	}

	/**
	 * get end
	 *
	 * @access public
	 */
	public function getEnd()
	{
		return $this->end;
	}

	/**
	 * get end as unix time
	 *
	 * @access public
	 * 
	 */
	public function getUTEnd()
	{
		include_once './Services/Calendar/classes/class.ilDateTime.php';
		$dt = new ilDateTime($this->end, IL_CAL_DATE, ilTimeZone::UTC);
		return $dt->get(IL_CAL_UNIX);
	}

	/**
	 * set room
	 *
	 * @access public
	 * @param string room
	 * 
	 */
	public function setRoom($a_room)
	{
		$this->room = $a_room;
	}

	/**
	 * get room
	 *
	 * @access public
	 * 
	 */
	public function getRoom()
	{
		return $this->room;
	}

	/**
	 * set cycle
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setCycle($a_cycle)
	{
		$this->cycle = $a_cycle;
	}

	/**
	 * get cycle
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getCycle()
	{
		return $this->cycle;
	}

}
?>