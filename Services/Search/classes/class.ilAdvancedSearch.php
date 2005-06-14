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
* Class ilAdvancedSearch
*
* Base class for advanced meta search
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilAdvancedSearch extends ilAbstractSearch
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
	function ilAdvancedSearch(&$qp_obj)
	{
		parent::ilAbstractSearch($qp_obj);
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

	function setOptions(&$options)
	{
		$this->options =& $options;
	}

	function &performSearch()
	{
		switch($this->getMode())
		{
			case 'educational':
				return $this->__searchEducational();
				break;

			default:
				echo "ilMDSearch::performSearch() no mode given";
				return false;
		}
	}


	function &__searchEducational()
	{
		$query = "SELECT rbac_id FROM il_meta_educational ";

		if(!strlen($where = $this->__createEducationalWhere()))
		{
			return $this->search_result;
		}
		$query = $query.$where;
		$res = $this->db->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array());
		}
		return $this->search_result;
	}

	function __createEducationalWhere()
	{
		$counter = 0;
		$where = 'WHERE ';


		if($this->options['int_type'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."interactivity_type = '".ilUtil::prepareDBString($this->options['int_type'])."' ");
		}
		if($this->options['lea_type'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."learning_resource_type = '".ilUtil::prepareDBString($this->options['lea_type'])."' ");
		}
		if($this->options['int_role'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."intended_end_user_role = '".ilUtil::prepareDBString($this->options['int_role'])."' ");
		}
		if($this->options['con'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."context = '".ilUtil::prepareDBString($this->options['con'])."' ");
		}
		return $counter ? $where : '';
	}

}
?>
