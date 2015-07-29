<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilObjiLincClassroom
* 
* @author Sascha Hofmann <saschahofmann@gmx.de> 
* @version $Id$
*
* @extends ilObject
*/

require_once ('./Services/Object/classes/class.ilObject.php');

class ilObjiLincClassroom extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	ilinc class id
	* @param	boolean	ilias ref id of ilinc course object
	*/
	function ilObjiLincClassroom($a_icla_id,$a_icrs_id)
	{
		global $ilErr,$ilias,$lng;
		
		$this->type = "icla";
		$this->id = $a_icla_id;		
		$this->parent = $a_icrs_id;

		$this->ilErr =& $ilErr;
		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->referenced = false;
		$this->call_by_reference = false;

		if (!empty($this->id))
		{
			$this->read();
		}
		
		return $this;
	}
	
	/**
	* 
	* @access private
	*/
	function read()
	{
		return true;
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update($a_data = "")
	{
		return true;
	}
	
	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		return true;
	}
} 