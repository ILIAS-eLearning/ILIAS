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

			case 'rights':
				return $this->__searchRights();
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
			return false;
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$query = $query.$where.$and;
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array());
		}
		return $this->search_result;
	}
	function &__searchRights()
	{
		$query = "SELECT rbac_id FROM il_meta_rights ";

		if(!strlen($where = $this->__createRightsWhere()))
		{
			return false;
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$query = $query.$where.$and;
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array());
		}
		return $this->search_result;
	}


	function __createRightsWhere()
	{
		$counter = 0;
		$where = 'WHERE ';


		if($this->options['costs'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."costs = '".ilUtil::prepareDBString($this->options['costs'])."' ");
		}
		if($this->options['copyright'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."copyright_and_other_restrictions = '".ilUtil::prepareDBString($this->options['copyright'])."' ");
		}
		return $counter ? $where : '';
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
		if($this->options['int_level_1'] or $this->options['int_level_2'])
		{
			$and = $counter++ ? 'AND ' : ' ';

			$fields = $this->__getDifference($this->options['int_level_1'],$this->options['int_level_2'],
											   array('VeryLow','Low','Medium','High','VeryHigh'));

			$where .= ($and."interactivity_level ".$this->__getInStatement($fields));
		}
		if($this->options['sem_1'] or $this->options['sem_2'])
		{
			$and = $counter++ ? 'AND ' : ' ';

			$fields = $this->__getDifference($this->options['sem_1'],$this->options['sem_2'],
											   array('VeryLow','Low','Medium','High','VeryHigh'));

			$where .= ($and."semantic_density ".$this->__getInStatement($fields));
		}
		if($this->options['dif_1'] or $this->options['dif_2'])
		{
			$and = $counter++ ? 'AND ' : ' ';

			$fields = $this->__getDifference($this->options['dif_1'],$this->options['dif_2'],
											 array('VeryEasy','Easy','Medium','Difficult','VeryDifficult'));

			$where .= ($and."difficulty ".$this->__getInStatement($fields));
		}

		return $counter ? $where : '';
	}

	function __getDifference($a_val1,$a_val2,$options)
	{
		$a_val2 = $a_val2 ? $a_val2 : count($options);
		// Call again if a > b
		if($a_val1 > $a_val2)
		{
			return $this->__getDifference($a_val2,$a_val1,$options);
		}

		$counter = 0;
		foreach($options as $option)
		{
			if($a_val1 > ++$counter)
			{
				continue;
			}
			if($a_val2 < $counter)
			{
				break;
			}
			$fields[] = $option;
		}
		return $fields ? $fields : array();
	}

	function __getInStatement($a_fields)
	{
		if(!$a_fields)
		{
			return '';
		}
		$in = " IN ('";
		$in .= implode("','",$a_fields);
		$in .= "') ";

		return $in;
	}

}
?>
