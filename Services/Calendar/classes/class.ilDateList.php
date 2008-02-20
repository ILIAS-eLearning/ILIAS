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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesCalendar 
*/

class ilDateList
{
	const TYPE_DATE = 1;
	const TYPE_DATETIME = 2;
	
	protected $list = array();

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
	}
	
	/**
	 * get
	 *
	 * @access public
	 * 
	 */
	public function get()
	{
	 	return $this->list ? $this->list : array();
	}
	
	/**
	 * add a date to the date list
	 *
	 * @access public
	 * @param object ilDateTime
	 */
	public function add(ilDateTime $date)
	{
	 	$this->list[$date->get(ilDateTime::FORMAT_UNIX)] = $date;
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
	 	$unix_remove = $remove->get(ilDateTime::FORMAT_UNIX);
		if(isset($this->list[$unix_remove]))
		{
			unset($this->list[$unix_remove]);
		}
		return true;
	}
	
	/**
	 * to string
	 *
	 * @access public
	 * 
	 */
	public function __toString()
	{
	 	$out = '';
	 	foreach($this->get() as $date)
	 	{
	 		$out .= $date->get(ilDateTime::FORMAT_DATE).'<br/>';
	 	}
	 	return $out;
	}
}

?>