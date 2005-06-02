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
* Class ilMetaDataSearch
*
* Base class for searching meta 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id
* 
* @package ilias-search
*
*/

class ilMetaDataSearch
{
	var $mode = '';

	/*
	 * instance of query parser
	 */
	var $query_parser = null;

	var $db = null;

	/**
	* Constructor
	* @access public
	*/
	function ilMetaDataSearch(&$qp_obj)
	{
		global $ilDB;
		
		$this->query_parser =& $qp_obj;
		$this->db =& $ilDB;

		include_once 'Services/Search/classes/class.ilSearchResult.php';

		$this->search_result = new ilSearchResult();
	}

	/**
	* set object type to search in
	* @param array Array of object types (e.g array('lm','st','pg','dbk'))
	* @access public
	*/
	function setFilter($a_filter)
	{
		if(is_array($a_filter))
		{
			$this->object_types = $a_filter;
		}
	}
	/**
	* get object type to search in
	* @param array Array of object types (e.g array('lm','st','pg','dbk'))
	* @access public
	*/
	function getFilter()
	{
		return $this->object_types ? $this->object_types : array();
	}

	/**
	* Define meta elements to search
	* 
	* @param array elements to search in. E.G array('keyword','contribute')
	* @access public
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	function getMode()
	{
		return $this->mode;
	}


	function &performSearch()
	{
		switch($this->getMode())
		{
			case 'keyword_contribute':
				return $this->__searchKeywordContribute();

			default:
				echo "ilMDSearch::performSearch() no mode given";
				return false;
		}
	}



	// Private
	function __createInStatement()
	{
		if(!$this->getFilter())
		{
			return '';
		}
		else
		{
			$type = "('";
			$type .= implode("','",$this->getFilter());
			$type .= "')";
			
			$in = " AND obj_type IN ".$type;

			return $in;
		}
	}

	function __searchKeywordsOnly()
	{
		$where = " WHERE ";
		$field = " keyword ";
		foreach($this->query_parser->getWords() as $word)
		{
			if($counter++)
			{
				$where .= strtoupper($this->query_parser->getCombination());
			}
			$where .= $field;
			$where .= ("LIKE ('%".$word."%')");
		}

		$query = "SELECT * FROM il_meta_keyword".
			$where.
			"ORDER BY meta_keyword_id DESC";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->obj_id,$row->obj_type,$row->rbac_id);
		}

		return $this->search_result;
	}		
}
?>
