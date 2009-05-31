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
* Auto completion class for user lists
*
*/
class ilUserAutoComplete
{
	/**
	* Get completion list
	*/
	static function getList($a_str)
	{
		global $ilDB;

		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		$result = new stdClass();
		$result->response = new stdClass();
		$result->response->results = array();
		if (strlen($a_str) < 3)
		{
			return ilJsonUtil::encode($result);
		}
		
		$set = $ilDB->query("SELECT login, firstname, lastname FROM usr_data WHERE ".
			$ilDB->like("login", "text", "%".$a_str."%")." OR ".
			$ilDB->like("firstname", "text", "%".$a_str."%")." OR ".
			$ilDB->like("lastname", "text", "%".$a_str."%").
			" ORDER BY login");
		$max = 20;
		$cnt = 0;
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			$result->response->results[$cnt] = new stdClass();
			$result->response->results[$cnt]->login = $rec["login"];
			$result->response->results[$cnt]->firstname = $rec["firstname"];
			$result->response->results[$cnt]->lastname = $rec["lastname"];
			$cnt++;
		}
		
		return ilJsonUtil::encode($result);
	}
	
}
?>
