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
		$in = $this->__createInStatement();
		$where = $this->__createWhereCondition();
		$locate = $this->__createLocateString();
		
		$query = "SELECT object_data.obj_id,object_data.type ".$locate."				  
				  FROM payment_objects 
				  INNER JOIN object_reference ON object_reference.ref_id = payment_objects.ref_id
				  INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id ";
		
		if($this->getFilterShopTopicId() != 0)
		{
			$where .= "	AND pt_topic_fk = ".$this->db->quote($this->getFilterShopTopicId())." ";
		}
					
		$query .= $where." ".$in.' ';		
		
		$query .= " GROUP BY object_data.obj_id ";		
		$query .= " ORDER BY object_data.obj_id DESC ";
		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->obj_id,$row->type,$this->__prepareFound($row));
		}		
		return $this->search_result;
	}

	public function __createInStatement()
	{
		$type = "('";
		$type .= implode("','",$this->object_types);
		$type .= "')";
		
		$in = " AND type IN ".$type;

		return $in;
	}
}
?>