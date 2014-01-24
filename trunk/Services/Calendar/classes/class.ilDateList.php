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
* List of dates
*  
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

class ilDateList implements Iterator
{
	const TYPE_DATE = 1;
	const TYPE_DATETIME = 2;
	
	protected $list_item = array();

	protected $type;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param type list of TYPE_DATE or type TYPE_DATETIME
	 * 
	 */
	public function __construct($a_type)
	{
	 	$this->type = $a_type;
	 	$this->list_item = array();
	}
	
	// Iterator
	/**
	 * Iterator Rewind
	 * @return 
	 */
	public function rewind()
	{
		reset($this->list_item);
	}
	
	/**
	 * Iterator Current
	 * @return 
	 */
	public function current()
	{
		return current($this->list_item);
	}
	
	/**
	 * Iterator key
	 * @return 
	 */
	public function key()
	{
		return key($this->list_item);
	}
	
	/**
	 * Iterator next
	 * @return 
	 */
	public function next()
	{
		return next($this->list_item);
	}
	
	/**
	 * Iterator valid
	 * @return 
	 */
	public function valid()
	{
		return $this->current() !== false;
	}
	
	
	/**
	 * get
	 *
	 * @access public
	 * 
	 */
	public function get()
	{
	 	return $this->list_item ? $this->list_item : array();
	}
	
	/**
	 * get item at specific position
	 *
	 * @access public
	 * @param int position (first position is 1)
	 * 
	 */
	public function getAtPosition($a_pos)
	{
	 	$counter = 1;
	 	foreach($this->get() as $item)
	 	{
	 		if($counter++ == $a_pos)
	 		{
	 			return $item;
	 		}
	 	}
	 	return null;
	}
	
	/**
	 * add a date to the date list
	 *
	 * @access public
	 * @param object ilDateTime
	 */
	public function add($date)
	{
	 	// the unix time is the key. 
	 	// It's casted to string because array_merge overwrites only string keys
	 	// @see merge
	 	$this->list_item[(string) $date->get(IL_CAL_UNIX)] = clone $date;
	}
	
	/**
	 * Merge two lists
	 *
	 * @access public
	 * @param object ilDateList
	 * 
	 */
	public function merge(ilDateList $other_list)
	{
		foreach($other_list->get() as $new_date)
		{
			$this->add($new_date);
		}
	}
	
	/**
	 * remove from list
	 *
	 * @access public
	 * @param object ilDateTime
	 * 
	 */
	public function remove(ilDateTime $remove)
	{
	 	$unix_remove = $remove->get(IL_CAL_UNIX);
		if(isset($this->list_item[$unix_remove]))
		{
			unset($this->list_item[$unix_remove]);
		}
		return true;
	}

	public function removeByDAY(ilDateTime $remove)
	{
		foreach($this->list_item as $key => $dt)
		{
			if(ilDateTime::_equals($remove, $dt, IL_CAL_DAY,ilTimeZone::UTC))
			{
				unset($this->list_item[$key]);
			}
		}
		return true;
	}
	
	/**
	 * Sort list
	 *
	 * @access public
	 * 
	 */
	public function sort()
	{
	 	return ksort($this->list_item,SORT_NUMERIC);
	}
	
	/**
	 * to string
	 *
	 * @access public
	 * 
	 */
	public function __toString()
	{
	 	$out = '<br />';
	 	foreach($this->get() as $date)
	 	{
	 		$out .= $date->get(IL_CAL_DATETIME,'','Europe/Berlin').'<br/>';
	 	}
	 	return $out;
	}
}

?>