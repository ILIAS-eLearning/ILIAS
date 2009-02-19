<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilObjectSearch extends ilAbstractSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilObjectSearch(&$qp_obj)
	{
		parent::ilAbstractSearch($qp_obj);

		$this->setFields(array('title','description'));
	}




	function &performSearch()
	{
		$in = $this->__createInStatement();
		$where = $this->__createWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT obj_id,type ".
			$locate.
			"FROM object_data ".
			$where." ".$in.' '.
			"ORDER BY obj_id DESC";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->obj_id,$row->type,$this->__prepareFound($row));
		}
		return $this->search_result;
	}



	// Protected can be overwritten in Like or Fulltext classes
	function __createInStatement()
	{
		$type = "('";
		$type .= implode("','",$this->object_types);
		$type .= "')";
		
		$in = " AND type IN ".$type;

		return $in;
	}
}
?>
