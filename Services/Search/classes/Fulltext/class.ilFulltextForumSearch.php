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
* Class ilFulltextLMContentSearch
*
* class for searching forum entries 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilForumSearch.php';

class ilFulltextForumSearch extends ilForumSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilFulltextForumSearch(&$qp_obj)
	{
		parent::ilForumSearch($qp_obj);
	}

	function __createAndCondition()
	{
		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$where .= " AND MATCH(content) AGAINST('";
			$prefix = $this->query_parser->getCombination() == 'and' ? '+' : '';
			foreach($this->query_parser->getWords() as $word)
			{
				$where .= $prefix;
				$where .= $word;
				$where .= '* ';
			}
			$where .= "' IN BOOLEAN MODE) ";
		}
		else
		{
			if($this->query_parser->getCombination() == 'or')
			{
				// i do not see any reason, but MATCH AGAINST(...) OR MATCH AGAINST(...) does not use an index
				$where .= " AND MATCH (content) AGAINST(' ";
				foreach($this->query_parser->getWords() as $word)
				{
					$where .= $word;
					$where .= ' ';
				}
				$where .= "') ";
			}
			else
			{
				$where .= " AND ";
				$counter = 0;
				foreach($this->query_parser->getWords() as $word)
				{
					if($counter++)
					{
						$where .= strtoupper($this->query_parser->getCombination());
					}
					$where .= " MATCH (content) AGAINST('";
					$where .= $word;
					$where .= "') ";
				}
			}
		}
		return $where;
	}		
}
?>
