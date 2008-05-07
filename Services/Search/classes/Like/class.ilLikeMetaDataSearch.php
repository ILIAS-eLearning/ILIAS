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
* Class ilLikeMetaDataSearch
*
* class for searching meta 
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id
* 
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilMetaDataSearch.php';

class ilLikeMetaDataSearch extends ilMetaDataSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilLikeMetaDataSearch(&$qp_obj)
	{
		parent::ilMetaDataSearch($qp_obj);
	}

	// Private
	function __createKeywordWhereCondition()
	{
		$concat = ' keyword ';
		$where = " WHERE ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= "OR";
			}
			$where .= $concat;
			$where .= (" LIKE ('%".$word."%')");
		}
		return $where;
	}		

	function __createContributeWhereCondition()
	{
		$concat = ' entity ';
		$where = " WHERE ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= "OR";
			}
			$where .= $concat;
			$where .= (" LIKE ('%".$word."%')");
		}
		return $where;
	}		
	function __createTitleWhereCondition()
	{
		#$concat = ' CONCAT(title,coverage) '; // broken if coverage is null
		// TODO: fix coverage search
		$concat = ' title ';
		
		$where = " WHERE ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= "OR";
			}
			$where .= $concat;
			$where .= (" LIKE ('%".$word."%')");
		}
		return $where;
	}

	function __createDescriptionWhereCondition()
	{
		$concat = ' description ';
		$where = " WHERE ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= "OR";
			}
			$where .= $concat;
			$where .= (" LIKE ('%".$word."%')");
		}
		return $where;
	}
}
?>
