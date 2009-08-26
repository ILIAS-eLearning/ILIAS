<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilShopObjectSearch
*
* @author Michael Jansen <mjansen@databay.de>* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilShopObjectSearch extends ilAbstractSearch
{
	private $filter_shop_topic_id = 0;
	
	public function __construct($qp_obj)
	{
		parent::__construct($qp_obj);
		$this->setFields(array('title', 'description'));
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
	
	public function performSearch()
	{
		$types = array();
		$values = array();
		
		$in = $this->__createInStatement();
		$where = $this->__createWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT object_reference.ref_id, object_data.obj_id,object_data.type ".$locate."			  
				  FROM payment_objects 
				  INNER JOIN object_reference ON object_reference.ref_id = payment_objects.ref_id
				  INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id ";
		
		$query .= $where['query'];
		$types = array_merge($types, $where['types']);
		$values = array_merge($values, $where['values']);
		$query .= $in;  

		$query .= " GROUP BY object_reference.ref_id, object_data.obj_id,object_data.type,object_data.title,object_data.description";		
		$query .= " ORDER BY object_data.obj_id DESC";

		$statement = $this->db->queryf(
			$query,
			$types,
			$values
		);

		while($row = $statement->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->ref_id, $row->type, $this->__prepareFound($row));
		}		
		return $this->search_result;
	}

	public function __createInStatement()
	{		
		return ' AND ' . $this->db->in('type', $this->object_types, false, 'text');
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