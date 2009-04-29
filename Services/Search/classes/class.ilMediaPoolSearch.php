<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Class ilMediaPoolSearch
*
* Abstract class for test search. Should be inherited by ilFulltextMediaPoolSearch
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilMediaPoolSearch extends ilAbstractSearch
{
	/**
	* Constructor
	* @access public
	*/
	function ilMediaPoolSearch(&$query_parser)
	{
		parent::ilAbstractSearch($query_parser);
	}



	function &performSearch()
	{
		$this->setFields(array('title','description'));

		$and = $this->__createAndCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT DISTINCT(mep_id) as mediapool_id ".
			$locate.
			"FROM object_data,mep_tree ".
			"WHERE obj_id = child ".
			$and." ".
			"GROUP BY mep_id";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->mediapool_id,'mep',$this->__prepareFound($row));
		}
		return $this->search_result;
	}
}
?>
