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

include_once 'Services/Search/classes/class.ilWikiContentSearch.php';

/**
* Class ilLikeWikiContentSearch
*
* class for searching media pool folders and titles of mob's
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* @package ilias-search
*
*/
class ilLikeWikiContentSearch extends ilWikiContentSearch
{

	/**
	* Constructor
	* @access public
	*/
	function __construct($qp_obj)
	{
		parent::__construct($qp_obj);
	}

	function __createWhereCondition()
	{
		global $ilDB;

		$and = "  WHERE ( ";
		$counter = 0;
		foreach($this->query_parser->getQuotedWords() as $word)
		{
			if($counter++)
			{
				$and .= " OR";
			}
			$and .= $this->db->like("content", "clob", '%'.$word.'%');
			$and .= " OR ";
			$and .= $this->db->like("title", "text", '%'.$word.'%');
		}
		return $and.") ";
	}
}
?>
