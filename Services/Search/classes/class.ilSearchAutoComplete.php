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
* Search Auto Completion Application Class
*
*/
include_once 'Services/Search/classes/class.ilSearchSettings.php';

class ilSearchAutoComplete
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
		
		$a_str = str_replace('"', "", $a_str);
		
		$settings = new ilSearchSettings();
		
		$object_types = array('cat','dbk','crs','fold','frm','grp','lm','sahs','glo','mep','htlm','exc','file','qpl','tst','svy','spl',
			'chat','icrs','icla','webr','mcst','sess','pg','st','wiki');

		$set = $ilDB->query("SELECT title, obj_id FROM object_data WHERE title LIKE ".
			$ilDB->quote($a_str."%", "text")." AND type in (".implode(ilUtil::quoteArray($object_types),",").") ORDER BY title");
//		$max = $settings->getAutoCompleteLength();
$max = 20;
		$cnt = 0;
		$list = array();
		$checked = array();
		$lim = "";
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			if (!in_array($rec["title"], $list) && !in_array($rec["obj_id"], $checked))
			{
				if (ilSearchAutoComplete::checkObjectPermission($rec["obj_id"]))
				{
					if (strpos($rec["title"], " ") > 0 || strpos($rec["title"], "-") > 0)
					{
						$rec["title"] = '"'.$rec["title"].'"';
					}
					$list[] = $lim.$rec["title"];
					$cnt++;
				}
				$checked[] = $rec["obj_id"];
			}
		}
		
		$set = $ilDB->query("SELECT rbac_id,obj_id,obj_type, keyword FROM il_meta_keyword WHERE keyword LIKE ".
			$ilDB->quote($a_str."%", "text")." AND obj_type in (".implode(ilUtil::quoteArray($object_types),",").") ORDER BY keyword"); 
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			if (!in_array($rec["keyword"], $list) && !in_array($rec["rbac_id"], $checked))
			{
				if (ilSearchAutoComplete::checkObjectPermission($rec["rbac_id"]))
				{
					if (strpos($rec["keyword"], " ") > 0)
					{
						$rec["keyword"] = '"'.$rec["keyword"].'"';
					}
					$list[] = $lim.$rec["keyword"];
					$cnt++;
				}
			}
			$checked[] = $rec["rbac_id"];
		}

		$i = 0;
		foreach ($list as $l)
		{
			$result->response->results[$i] = new stdClass();
			$result->response->results[$i]->term = $l;
			$i++;
		}

		return ilJsonUtil::encode($result);
	}
	
	/**
	* Checks read permission on obj id
	*/
	static function checkObjectPermission($a_obj_id)
	{
		global $ilAccess;
		
		$refs = ilObject::_getAllReferences($a_obj_id);
		foreach ($refs as $ref)
		{
			if ($ilAccess->checkAccess("read", "", $ref))
			{
				return true;
			}
		}
		return false;
	}
}
?>
