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

include_once 'Services/Search/classes/class.ilAdvancedSearch.php';

/**
* Class ilLikeMetaDataSearch
*
* class for searching meta 
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id
* 
* @package ilias-search
*
*/
class ilLikeAdvancedSearch extends ilAdvancedSearch
{
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct($qp)
	{
		parent::__construct($qp);
	}
	
	function __createTaxonWhereCondition()
	{
		global $ilDB;
		
		if($this->options['lom_taxon'])
		{
			$where = " WHERE (";
			
			$counter = 0;
			foreach($this->query_parser->getQuotedWords() as $word)
			{
				if($counter++)
				{
					$where .= "OR";
				}
				
				$where .= $ilDB->like('taxon','text','%'.$word.'%');
			}
			$where .= ') ';
			return $where;
		}
		return '';
	}
	
	function __createKeywordWhereCondition()
	{
		global $ilDB;
		
		$where = " WHERE (";
		
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= "OR";
			}
			
			$where .= $ilDB->like('keyword','text','%'.$word.'%');
		}
		$where .= ') ';
		return $where;
	}
	
	function __createLifecycleWhereCondition()
	{
		global $ilDB;
		
		if($this->options['lom_version'])
		{
			$where = " WHERE (";
			
			$counter = 0;
			foreach($this->query_parser->getQuotedWords() as $word)
			{
				if($counter++)
				{
					$where .= "OR";
				}
				
				$where .= $ilDB->like('meta_version','text','%'.$word.'%');
			}
			$where .= ') ';
			return $where;
		}
		return '';
	}
	
	function __createEntityWhereCondition()
	{
		global $ilDB;

		if($this->options['lom_role_entry'])
		{
			$where = " WHERE (";
			
			$counter = 0;
			foreach($this->query_parser->getQuotedWords() as $word)
			{
				if($counter++)
				{
					$where .= "OR";
				}
				
				$where .= $ilDB->like('entity','text','%'.$word.'%');
			}
			$where .= ') ';
			return $where;
		}
		return '';
	}

	function __createCoverageAndCondition()
	{
		global $ilDB;

		if($this->options['lom_coverage'])
		{
			$where = " AND (";
			
			$counter = 0;
			foreach($this->query_parser->getQuotedWords() as $word)
			{
				if($counter++)
				{
					$where .= "OR";
				}
				
				$where .= $ilDB->like('coverage','text','%'.$word.'%');
			}
			$where .= ') ';
			return $where;
		}
		return '';
	}
	
	function __createTitleDescriptionWhereCondition()
	{
		global $ilDB;
		
		$concat = $ilDB->concat(
			array(
				array('title','text'),
				array('description','text')));

		$where = " WHERE (";

		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$where .= "OR";
			}
			
			$where .= $ilDB->like($concat,'text','%'.$word.'%');
		}
		$where .= ') ';
		
		return $where;
	}		
	

}
