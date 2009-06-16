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

include_once 'Services/Search/classes/class.ilShopMetaDataSearch.php';

/**
* Class ilLikeShopMetaDataSearch
*
* class for searching meta in shop objects
*
* @author Michael Jansen <mjansen@databay.de>
* @package ilias-search
*
*/
class ilLikeShopMetaDataSearch extends ilShopMetaDataSearch
{
	public function __construct($qp_obj)
	{
		parent::__construct($qp_obj);
	}

	public function __createKeywordWhereCondition()
	{
		$where = '';
		$types = array();
		$values = array();	
		
		$where .= ' WHERE (payment_objects.status = 1 OR payment_objects.status = 2) AND (';
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= 'OR';
			}
			
			$where .= $this->db->like(' keyword', 'text', '%%'.$word.'%%');
		}
		
		if($this->getFilterShopTopicId() != 0)
		{
			$where .= '	AND pt_topic_fk = %s ';
			$types[] = 'integer';
			$values[] = $this->getFilterShopTopicId();			 
		}
		
		return array(
			'query' => $where.')',
			'types' => $types,
			'values' => $values
		);
	}		

	public function __createContributeWhereCondition()
	{
		$where = '';
		$types = array();
		$values = array();	
		
		$where .= ' WHERE (payment_objects.status = 1 OR payment_objects.status = 2) AND (';
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= 'OR';
			}
			$where .= $this->db->like(' entity', 'text', '%%'.$word.'%%');
		}
		
		if($this->getFilterShopTopicId() != 0)
		{
			$where .= '	AND pt_topic_fk = %s ';
			$types[] = 'integer';
			$values[] = $this->getFilterShopTopicId();			 
		}
		
		return array(
			'query' => $where.')',
			'types' => $types,
			'values' => $values
		);
	}
		
	public function __createTitleWhereCondition()
	{
		$where = '';
		$types = array();
		$values = array();	
				
		$where .= ' WHERE (payment_objects.status = 1 OR payment_objects.status = 2) AND (';
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= 'OR';
			}
			$where .= $this->db->like(' title', 'text', '%%'.$word.'%%');
		}
		
		if($this->getFilterShopTopicId() != 0)
		{
			$where .= '	AND pt_topic_fk = %s ';
			$types[] = 'integer';
			$values[] = $this->getFilterShopTopicId();			 
		}
		
		return array(
			'query' => $where.')',
			'types' => $types,
			'values' => $values
		);
	}

	public function __createDescriptionWhereCondition()
	{
		$where = '';
		$types = array();
		$values = array();	
		
		$where = ' WHERE (payment_objects.status = 1 OR payment_objects.status = 2) AND (';
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= 'OR';
			}
			$where .= $this->db->like(' description', 'text', '%%'.$word.'%%');
		}
		
		if($this->getFilterShopTopicId() != 0)
		{
			$where .= '	AND pt_topic_fk = %s ';
			$types[] = 'integer';
			$values[] = $this->getFilterShopTopicId();			 
		}
		
		return array(
			'query' => $where.')',
			'types' => $types,
			'values' => $values
		);
	}
}
?>
