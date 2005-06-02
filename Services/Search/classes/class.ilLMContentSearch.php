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
* Class ilLMContentSearch
*
* Abstract class for lm content. Should be inherited by ilFulltextLMContentSearch
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-search
*
*/

class ilLMContentSearch
{
	/*
	 * instance of query parser
	 */
	var $query_parser = null;

	/**
	* Constructor
	* @access public
	*/
	function ilLMContentSearch(&$query_parser)
	{
		global $ilDB;

		$this->query_parser =& $query_parser;
		$this->db =& $ilDB;


		include_once 'Services/Search/classes/class.ilSearchResult.php';

		$this->search_result = new ilSearchResult();
	}

	function &performSearch()
	{
		$in = $this->__createInStatement();
		$and = $this->__createAndCondition();

		$query = "SELECT lm_id,parent_type FROM lm_data as ld,page_object as po WHERE ld.obj_id = po.page_id ";
		
		$res = $this->db->query($query.$and.$in);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->lm_id,$row->parent_type);
		}

		return $this->search_result;
	}



	// Protected can be overwritten in Like or Fulltext classes
	function __createInStatement()
	{
		return " AND parent_type IN('lm','dbk')";
	}

	function __createAndCondition()
	{
		echo "Overwrite me!";
	}

}
?>
