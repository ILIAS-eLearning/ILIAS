<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Saves usages of page content elements in pages
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup 
*/
class ilPageContentUsage
{
	/**
	* Save usages
	*/
	static function saveUsage($a_pc_type, $a_pc_id, $a_usage_type, $a_usage_id, $a_usage_hist_nr = 0)
	{
		global $ilDB;
		
		$ilDB->replace("page_pc_usage", array (
			"pc_type" => array("text", $a_pc_type),
			"pc_id" => array("integer", (int) $a_pc_id),
			"usage_type" => array("text", $a_usage_type),
			"usage_id" => array("integer", (int) $a_usage_id),
			"usage_hist_nr" => array("integer", (int) $a_usage_hist_nr)
			),array());
	}

	/**
	* Delete all usages
	*/
	static function deleteAllUsages($a_pc_type, $a_usage_type, $a_usage_id, $a_usage_hist_nr = 0)
	{
		global $ilDB;
		
		$ilDB->manipulate($q = "DELETE FROM page_pc_usage WHERE usage_type = ".
			$ilDB->quote($a_usage_type, "text").
			" AND usage_id = ".$ilDB->quote((int) $a_usage_id, "integer").
			" AND usage_hist_nr = ".$ilDB->quote((int) $a_usage_hist_nr, "integer").
			" AND pc_type = ".$ilDB->quote($a_pc_type, "text"));
	}
	
	/**
	* Get usages
	*/
	function getUsages($a_pc_type, $a_pc_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM page_pc_usage ".
			" WHERE pc_type = ".$ilDB->quote($a_pc_type, "text").
			" AND pc_id = ".$ilDB->quote($a_pc_id, "integer")
			);
		$usages = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$usages[] = $rec;
		}
		return $usages;
	}
	
}
