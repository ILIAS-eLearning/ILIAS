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

class ilObjectSearch
{
	/*
	 *
	 * List of all searchable objects
	 *
	 */
	var $object_types = array('cat','dbk','crs','fold','frm','grp','lm','sahs','glo','mep','html','exc','file','qpl','tst','svy','spl',
						 'chat','icrs','icla','webr');


	/*
	 * instance of query parser
	 */
	var $qp_obj = null;

	/**
	* Constructor
	* @access public
	*/
	function ilObjectSearch(&$qp_obj)
	{

		global $ilDB;

		$this->qp_obj =& $qp_obj;
		
		$this->db =& $ilDB;


		include_once 'Services/Search/classes/class.ilSearchResult.php';

		$this->search_result = new ilSearchResult();
	}

	function enableKeywords($a_mode)
	{
		$this->keyword_search = $a_mode;
	}
	function enabledKeywords()
	{
		return $this->keyword_search;
	}

	function &performSearch()
	{
		$in = $this->__createInStatement();
		$where = $this->__createWhereCondition();

		$query = "SELECT obj_id,type FROM object_data ".
			$where." ".$in.' '.
			"ORDER BY obj_id DESC";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->obj_id,$row->type);
		}

		return $this->search_result;
	}



	// Private
	function __createInStatement()
	{
		$type = "('";
		$type .= implode("','",$this->object_types);
		$type .= "')";
		
		$in = " AND type IN ".$type;

		return $in;
	}

	function __createWhereCondition()
	{
		$concat  = " CONCAT(";
		$concat .= 'title,description';
		$concat .= ") ";

		$where = "WHERE ";
		foreach($this->qp_obj->getWords() as $word)
		{
			if($counter++)
			{
				$where .= strtoupper($this->qp_obj->getCombination());
			}
			$where .= $concat;
			$where .= ("LIKE ('%".$word."%')");
		}
		return $where;
	}

}
?>
