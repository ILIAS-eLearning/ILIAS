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
* Class ilFulltextShopMetaDataSearch
*
* class for searching meta in shop objects
*
* @author Michael Jansen <mjansen@databay.de> 
* @package ilias-search
*
*/
class ilFulltextShopMetaDataSearch extends ilShopMetaDataSearch
{

	public function __construct($qp_obj)
	{
		parent::__construct($qp_obj);
	}

	public function __createKeywordWhereCondition()
	{
		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= $word;
					$agaings_str .= '* ';
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (keyword) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "' IN BOOLEAN MODE) ";
			}
		}
		else
		{
			// i do not see any reason, but MATCH AGAINST(...) OR MATCH AGAINST(...) does not use an index
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= ($word.' ');
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (keyword) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "') ";
			}
		}
		return $query;
	}
		
	public function __createContributeWhereCondition()
	{
		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= $word;
					$agaings_str .= '* ';
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (entity) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "' IN BOOLEAN MODE) ";
			}
		}
		else
		{
			// i do not see any reason, but MATCH AGAINST(...) OR MATCH AGAINST(...) does not use an index
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= ($word.' ');
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (entity) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "') ";
			}
		}
		return $query;
	}
	
	public function __createTitleWhereCondition()
	{
		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= $word;
					$agaings_str .= '* ';
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (title,coverage) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "' IN BOOLEAN MODE) ";
			}
		}
		else
		{
			// i do not see any reason, but MATCH AGAINST(...) OR MATCH AGAINST(...) does not use an index
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= ($word.' ');
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (title,coverage) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "') ";
			}
		}
		return $query;
	}
	
	public function __createDescriptionWhereCondition()
	{
		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= $word;
					$agaings_str .= '* ';
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (description) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "' IN BOOLEAN MODE) ";
			}
		}
		else
		{
			// i do not see any reason, but MATCH AGAINST(...) OR MATCH AGAINST(...) does not use an index
			$query .= " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) ";
			
			$agaings_str = '';
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				if(strlen($word))
				{
					$agaings_str .= ($word.' ');
				}
			}
			if(strlen($agaings_str))
			{
				$query .= " AND MATCH (description) AGAINST(' ";
				$query .= $agaings_str;
				$query .= "') ";
			}
		}
		return $query;
	}		
}
?>
