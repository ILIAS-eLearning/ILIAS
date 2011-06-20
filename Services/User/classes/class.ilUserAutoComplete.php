<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Auto completion class for user lists
*
*/
class ilUserAutoComplete
{
	/**
	* Get completion list
	*/
	public static function getList($a_str)
	{
		global $ilDB;
		
		/*
		$search = explode(',', $a_str);
		if(count($search) > 1)
		{
			$a_str = end($search);
		}
		*/

		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		$result = new stdClass();
		$result->response = new stdClass();
		$result->response->results = array();
		if (strlen($a_str) < 3)
		{
			return ilJsonUtil::encode($result);
		}

		$serach = ' ';
		foreach(array('firstname','lastname','email') as $field)
		{
			include_once './Services/Search/classes/class.ilUserSearchOptions.php';
			if(ilUserSearchOptions::_isSearchable($field))
			{
				$search .= $ilDB->like($field,'text',$a_str.'%').' OR ';
			}
		}
		$search .= $ilDB->like("login", "text", $a_str."%")." ";


		
		include_once './Services/User/classes/class.ilUserAccountSettings.php';
		if(ilUserAccountSettings::getInstance()->isUserAccessRestricted())
		{
			include_once './Services/User/classes/class.ilUserFilter.php';
			$query = "SELECT login, firstname, lastname, email FROM usr_data ".
				"WHERE (".
				$search.
				#$ilDB->like("login", "text", $a_str."%")." OR ".
				#$ilDB->like("firstname", "text", $a_str."%")." OR ".
				#$ilDB->like("lastname", "text", $a_str."%").
				") AND ".$ilDB->in('time_limit_owner',ilUserFilter::getInstance()->getFolderIds(),false,'integer')." ".
				"ORDER BY login ";
			$set = $ilDB->query($query);
		}
		else
		{
			$set = $ilDB->query("SELECT login, firstname, lastname, email FROM usr_data WHERE ".
				$search.
				#$ilDB->like("login", "text", $a_str."%")." OR ".
				#$ilDB->like("firstname", "text", $a_str."%")." OR ".
				#$ilDB->like("lastname", "text", $a_str."%").
				" ORDER BY login");
		}
		$max = 20;
		$cnt = 0;
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			$result->response->results[$cnt] = new stdClass();
			$result->response->results[$cnt]->login = $rec["login"];
			$result->response->results[$cnt]->firstname = $rec["firstname"];
			$result->response->results[$cnt]->lastname = $rec["lastname"];
			$result->response->results[$cnt]->email = $rec["email"];
			$cnt++;
		}
		
		return ilJsonUtil::encode($result);
	}
	
}
?>
