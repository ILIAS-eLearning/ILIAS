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
* Class ilWikiContentSearch
*
* Abstract class for wiki content. Should be inherited by ilFulltextWikiContentSearch
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilWikiContentSearch extends ilAbstractSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilWikiContentSearch(&$query_parser)
	{
		global $ilDB;

		parent::ilAbstractSearch($query_parser);
	}

	function &performSearch()
	{
		$this->setFields(array('content'));

		$in = $this->__createInStatement();
		$where = $this->__createWhereCondition();
		$locate = $this->__createLocateString();

		$query = "SELECT page_id,parent_id,parent_type ".
			$locate.
			"FROM page_object, il_wiki_page ".
			$where.
			"AND il_wiki_page.id = page_object.page_id ".
			$in;
			
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->search_result->addEntry($row->parent_id,$row->parent_type,$this->__prepareFound($row),$row->page_id);
		}

		return $this->search_result;
	}



	// Protected can be overwritten in Like or Fulltext classes
	function __createInStatement()
	{
		if(!$this->getFilter())
		{
			return '';
		}
		else
		{
			$type = "('";
			$type .= implode("','",$this->getFilter());
			$type .= "')";
			
			$in = " AND parent_type IN ".$type;

			return $in;
		}
	}

	function __createAndCondition()
	{
		echo "Overwrite me!";
	}

}
?>
