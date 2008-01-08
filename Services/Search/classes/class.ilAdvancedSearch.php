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
			case 'requirement':
				return $this->__searchRequirement();
				break;

			case 'educational':
				return $this->__searchEducational();
				break;

			case 'typical_age_range':
				return $this->__searchTypicalAgeRange();
				break;

			case 'rights':
				return $this->__searchRights();
				break;

			case 'classification':
				return $this->__searchClassification();
				break;

			case 'taxon':
				return $this->__searchTaxon();
				break;

			case 'keyword':
				return $this->__searchKeyword(true);
				break;

			case 'format':
				return $this->__searchFormat();
				break;

			case 'lifecycle':
				return $this->__searchLifecycle();
				break;

			case 'contribute':
				return $this->__searchContribute();
				break;

			case 'entity':
				return $this->__searchEntity();
				break;

			case 'general':
				return $this->__searchGeneral();
				break;

			case 'keyword_all':
				return $this->__searchKeyword(false);
				break;

			case 'title_description':
				return $this->__searchTitleDescription();
				break;

			case 'language':
				return $this->__searchLanguage();
				break;

			default:
				echo "ilMDSearch::performSearch() no mode given";
				return false;
		}
	}

	function &__searchTitleDescription()
	{
		if(!$this->options['title'])
		{
			return false;
		}
		$this->setFields(array('title','description'));

		$and = ("AND type ".$this->__getInStatement($this->getFilter()));
		$where = $this->__createTitleDescriptionWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT obj_id,type ".
			$locate.
			"FROM object_data ".
			$where." ".$and.' '.
			"ORDER BY obj_id DESC";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->obj_id,$row->type,$this->__prepareFound($row));
		}

		return $this->search_result;
	}

	function &__searchGeneral()
	{
		if(!$this->options['coverage'] and !$this->options['structure'])
		{
			return false;
		}
		if($this->options['coverage'])
		{
			$this->setFields(array('coverage'));
			$and = $this->__createCoverageAndCondition();
			$locate = $this->__createLocateString();
		}
		if($this->options['structure'])
		{
			$and .= ("AND general_structure = '".ilUtil::prepareDBString($this->options['structure'])."' ");
		}
			
		$query = "SELECT rbac_id,obj_type,obj_id ".
			$locate." ".
			"FROM il_meta_general ".
			"WHERE obj_type ".$this->__getInStatement($this->getFilter())." ".
			$and;

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			if($this->options['coverage'])
			{
				$found = $this->__prepareFound($row);
				if(!in_array(0,$found))
				{
					$this->search_result->addEntry($row->rbac_id,$row->obj_type,$found,$row->obj_id);
				}
			}
			else
			{
				$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
			}
		}

		return $this->search_result;
	}

	function &__searchLanguage()
	{
		if(!$this->options['language'])
		{
			return false;
		}

		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_language ".
			"WHERE language = '".ilUtil::prepareDBString($this->options['language'])."' ".
			"AND obj_type ".$this->__getInStatement($this->getFilter()).' '.
			"AND parent_type = 'meta_general'";

		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchContribute()
	{
		if(!$this->options['role'])
		{
			return false;
		}

		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_contribute ".
			"WHERE role = '".ilUtil::prepareDBString($this->options['role'])."' ".
			"AND obj_type ".$this->__getInStatement($this->getFilter());

		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchEntity()
	{
		$this->setFields(array('entity'));

		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$where = $this->__createEntityWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT rbac_id,obj_id,obj_type ".
			$locate.
			"FROM il_meta_entity ".
			$where." ".$and.' ';

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$found = $this->__prepareFound($row);
			if(!in_array(0,$found))
			{
				$this->search_result->addEntry($row->rbac_id,$row->obj_type,$found,$row->obj_id);
			}
		}

		return $this->search_result;
	}



	function &__searchRequirement()
	{
		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_requirement ";

		if(!strlen($where = $this->__createRequirementWhere()))
		{
			return false;
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$query = $query.$where.$and;
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchEducational()
	{
		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_educational ";

		if(!strlen($where = $this->__createEducationalWhere()))
		{
			return false;
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$query = $query.$where.$and;
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchTypicalAgeRange()
	{
		if(!$this->options['typ_age_1'] or !$this->options['typ_age_2'])
		{
			return false;
		}

		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_typical_age_range ".
			"WHERE typical_age_range_min >= '".(int) $this->options['typ_age_1']."' ".
			"AND typical_age_range_max <= '".(int) $this->options['typ_age_2']."'";


		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchRights()
	{
		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_rights ";

		if(!strlen($where = $this->__createRightsWhere()))
		{
			return false;
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$query = $query.$where.$and;
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchClassification()
	{
		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_classification ";

		if(!strlen($where = $this->__createClassificationWhere()))
		{
			return false;
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$query = $query.$where.$and;
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
		}
		return $this->search_result;
	}

	function &__searchTaxon()
	{
		$this->setFields(array('taxon'));

		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		$where = $this->__createTaxonWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT rbac_id,obj_id,obj_type ".
			$locate.
			"FROM il_meta_taxon ".
			$where." ".$and.' ';

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$found = $this->__prepareFound($row);
			if(!in_array(0,$found))
			{
				$this->search_result->addEntry($row->rbac_id,$row->obj_type,$found,$row->obj_id);
			}
		}

		return $this->search_result;
	}

	function &__searchKeyword($a_in_classification = false)
	{
		$this->setFields(array('keyword'));

		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		if($a_in_classification)
		{
			$and .= " AND parent_type = 'meta_classification' ";
		}
		$where = $this->__createKeywordWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT rbac_id,obj_id,obj_type ".
			$locate.
			"FROM il_meta_keyword ".
			$where." ".$and.' ';

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$found = $this->__prepareFound($row);
			if(!in_array(0,$found) or !$a_in_classification)
			{
				$this->search_result->addEntry($row->rbac_id,$row->obj_type,$found,$row->obj_id);
			}
		}

		return $this->search_result;
	}
	function &__searchLifecycle()
	{
		$this->setFields(array('meta_version'));

		if($this->options['version'])
		{
			$where = $this->__createLifecycleWhereCondition();
			$locate = $this->__createLocateString();
		}
		else
		{
			$where = "WHERE 1 ";
		}
		$and = ("AND obj_type ".$this->__getInStatement($this->getFilter()));
		
		if($this->options['status'])
		{
			$and .= (" AND lifecycle_status = '".$this->options['status']."'");
		}

		$query = "SELECT rbac_id,obj_id,obj_type ".
			$locate.
			"FROM il_meta_lifecycle ".
			$where." ".$and.' ';

		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$found = $this->__prepareFound($row);
			if(!in_array(0,$found))
			{
				$this->search_result->addEntry($row->rbac_id,$row->obj_type,$found,$row->obj_id);
			}
		}

		return $this->search_result;
	}

	function &__searchFormat()
	{
		if(!$this->options['format'])
		{
			return false;
		}

		$query = "SELECT rbac_id,obj_id,obj_type FROM il_meta_format ".
			"WHERE format LIKE('".ilUtil::prepareDBString($this->options['format'])."') ".
			"AND obj_type ".$this->__getInStatement($this->getFilter());
		
		$res = $this->db->query($query);
		#var_dump("<pre>",$query,"<pre>");
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->rbac_id,$row->obj_type,array(),$row->obj_id);
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
	function __createClassificationWhere()
	{
		$counter = 0;
		$where = 'WHERE ';


		if($this->options['purpose'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."purpose = '".ilUtil::prepareDBString($this->options['purpose'])."' ");
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
	function __createRequirementWhere()
	{
		$counter = 0;
		$where = 'WHERE ';


		if($this->options['os'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."operating_system_name = '".ilUtil::prepareDBString($this->options['os'])."' ");
		}
		if($this->options['browser'])
		{
			$and = $counter++ ? 'AND ' : ' ';
			$where .= ($and."browser_name = '".ilUtil::prepareDBString($this->options['browser'])."' ");
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
