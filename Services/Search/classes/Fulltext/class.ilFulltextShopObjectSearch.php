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

include_once 'Services/Search/classes/class.ilShopObjectSearch.php';

/**
* Class ilFulltextShopObjectSearch
*
* Performs Mysql fulltext search in object_data title and description
*
* @author Michael Jansen <mjansen@databay.de> 
* @package ilias-search
*
*/
class ilFulltextShopObjectSearch extends ilShopObjectSearch
{
	public function __construct($qp_obj)
	{
		parent::__construct($qp_obj);
	}

	public function __createWhereCondition()
	{		
		if($this->db->isMysql4_0OrHigher())
		{
			$where = " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) AND MATCH (title,description) AGAINST(' ";
			
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				$where .= $word;
				$where .= '* ';
			}
			$where .= "' IN BOOLEAN MODE) ";
			
			return $where;
		}
		else
		{
			$where = " WHERE (payment_objects.status = 1 OR payment_objects.status = 2) AND MATCH (title,description) AGAINST(' ";
			
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				$where .= ($word.' ');
			}
			$where .= "')";
			
			return $where;
		}
	}
}
?>