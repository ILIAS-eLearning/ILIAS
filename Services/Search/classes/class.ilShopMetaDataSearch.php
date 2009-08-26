<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once 'Services/Search/classes/class.ilAbstractSearch.php';

/**
* Class ilShopMetaDataShopSearch
*
* @author Michael Jansen <mjansen@databay.de>
* @package ilias-search
*
*/
class ilShopMetaDataSearch extends ilAbstractSearch
{
	public $mode = '';

	/*
	 * instance of query parser
	 */
	public $query_parser = null;

	public $db = null;
	
	private $filter_shop_topic_id = 0;

	public function __construct($qp_obj)
	{
		parent::__construct($qp_obj);
	}
	
	public function setCustomSearchResultObject($a_search_result_obect)
	{
		$this->search_result = $a_search_result_obect;
	}	
	
	public function setFilterShopTopicId($a_topic_id)
	{
		$this->filter_shop_topic_id = $a_topic_id;
	}	
	public function getFilterShopTopicId()
	{
		return $this->filter_shop_topic_id;
	}

	public function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	public function getMode()
	{
		return $this->mode;
	}	

	public function performSearch()
	{
		switch($this->getMode())
		{
			case 'keyword':
				return $this->__searchKeywords();

			case 'contribute':
				return $this->__searchContribute();

			case 'title':
				return $this->__searchTitles();

			case 'description':
				return $this->__searchDescriptions();

			default:
				echo __METHOD__.' no mode given';
				return false;
		}
	}
	
	private function __createInStatement()
	{
		if(!$this->getFilter())
		{
			return '';
		}
		else
		{
			return ' AND ' . $this->db->in('obj_type', $this->getFilter(), false, 'text');
		}
	}
	
	private function __searchContribute()
	{
		$types = array();
		$values = array();
		
		$this->setFields(array('entity'));

		$in = $this->__createInStatement();
		$where = $this->__createContributeWhereCondition();
		$locate = $this->__createLocateString();
		
		$query = "SELECT object_reference.ref_id,rbac_id,il_meta_entity.obj_id,obj_type ".$locate."				  
				  FROM payment_objects 
				  INNER JOIN object_reference ON object_reference.ref_id = payment_objects.ref_id
				  INNER JOIN il_meta_entity ON il_meta_entity.obj_id = object_reference.obj_id ";
		
		$query .= $where['query'];
		$types = array_merge($types, $where['types']);
		$values = array_merge($values, $where['values']);
		$query .= $in;  
		
		$query .= " GROUP BY object_reference.ref_id,il_meta_entity.obj_id,rbac_id,obj_type,il_meta_entity.entity ";

		$statement = $this->db->queryf(
			$query,
			$types,
			$values
		);

		while($row = $statement->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->ref_id,$row->obj_type,$this->__prepareFound($row),$row->obj_id);
		}

		return $this->search_result;
	}

	private function __searchKeywords()
	{
		$types = array();
		$values = array();
		
		$this->setFields(array('keyword'));

		$in = $this->__createInStatement();
		$where = $this->__createKeywordWhereCondition();
		$locate = $this->__createLocateString();
		
		$query = "SELECT object_reference.ref_id,rbac_id,il_meta_keyword.obj_id,obj_type ".$locate."				  
				  FROM payment_objects 
				  INNER JOIN object_reference ON object_reference.ref_id = payment_objects.ref_id
				  INNER JOIN il_meta_keyword ON il_meta_keyword.obj_id = object_reference.obj_id ";
		
		$query .= $where['query'];
		$types = array_merge($types, $where['types']);
		$values = array_merge($values, $where['values']);
		$query .= $in;
		
		$query .= " GROUP BY object_reference.ref_id,il_meta_keyword.obj_id,rbac_id,obj_type,il_meta_keyword.keyword ";

		$statement = $this->db->queryf(
			$query,
			$types,
			$values
		);

		while($row = $statement->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->ref_id,$row->obj_type,$this->__prepareFound($row),$row->obj_id);
		}

		return $this->search_result;
	}
	
	private function __searchTitles()
	{
		$types = array();
		$values = array();
		
		$this->setFields(array('title'));

		$in = $this->__createInStatement();
		$where = $this->__createTitleWhereCondition();
		$locate = $this->__createLocateString();
		
		$query = "SELECT object_reference.ref_id,rbac_id,il_meta_general.obj_id,obj_type ".$locate."				  
				  FROM payment_objects 
				  INNER JOIN object_reference ON object_reference.ref_id = payment_objects.ref_id
				  INNER JOIN il_meta_general ON il_meta_general.obj_id = object_reference.obj_id ";
		
		$query .= $where['query'];
		$types = array_merge($types, $where['types']);
		$values = array_merge($values, $where['values']);
		$query .= $in;		
		
		$query .= " GROUP BY object_reference.ref_id,il_meta_general.obj_id,rbac_id,obj_type,il_meta_general.title ";

		$statement = $this->db->queryf(
			$query,
			$types,
			$values
		);
		
		while($row = $statement->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->ref_id,$row->obj_type,$this->__prepareFound($row),$row->obj_id);
		}

		return $this->search_result;
	}		
	
	private function __searchDescriptions()
	{
		$types = array();
		$values = array();
		
		$this->setFields(array('description'));

		$in = $this->__createInStatement();
		$where = $this->__createDescriptionWhereCondition();
		$locate = $this->__createLocateString();
		
		$query = "SELECT object_reference.ref_id,rbac_id,il_meta_description.obj_id,obj_type ".$locate."				  
				  FROM payment_objects 
				  INNER JOIN object_reference ON object_reference.ref_id = payment_objects.ref_id
				  INNER JOIN il_meta_description ON il_meta_description.obj_id = object_reference.obj_id ";
		
		$query .= $where['query'];
		$types = array_merge($types, $where['types']);
		$values = array_merge($values, $where['values']);
		$query .= $in;
		
		$query .= " GROUP BY object_reference.ref_id,il_meta_description.obj_id,rbac_id,obj_type,il_meta_description.description ";

		$statement = $this->db->queryf(
			$query,
			$types,
			$values
		);
		
		while($row = $statement->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->ref_id,$row->obj_type,$this->__prepareFound($row),$row->obj_id);
		}

		return $this->search_result;
	}	
	
	/**
	* build locate string in case of AND search
	* @return string 
	* @access public
	*/
	public function __createLocateString()
	{
		global $ilDB;
		
		if($this->query_parser->getCombination() == 'or')
		{
			return '';
		}
		
		if(!strlen($this->query_parser->getQueryString()))
		{
			return '';
		}
		
		$locate = '';
		
		if(count($this->fields) > 1)
		{
			$tmp_fields = array();
			foreach($this->fields as $field)
			{
				$tmp_fields[] = array($field,'text');
			}
			$complete_str = $ilDB->concat($tmp_fields);
		}
		else
		{
			$complete_str = $this->fields[0];
		}

		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			$locate .= ',';
			$locate .= $ilDB->locate($ilDB->quote($word, 'text'), $complete_str, 1);
			$locate .= (' found'.$counter++);
			$locate .= ' ';
		}
		
		return $locate;
	}

	public function __prepareFound(&$row)
	{
		if($this->query_parser->getCombination() == 'or')
		{
			return array();
		}
		
		if(!strlen($this->query_parser->getQueryString()))
		{
			return array();
		}
		
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			$res_found = 'found'.$counter++;
			$found[] = $row->$res_found;
		}
		return $found ? $found : array();
	}	
}
?>
