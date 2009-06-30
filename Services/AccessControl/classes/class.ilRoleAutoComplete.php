<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Auto completion class for user lists
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilRoleAutoComplete
{
	/**
	* Get completion list
	*/
	public static function getList($a_str)
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
		
		$ilDB->setLimit(20);
		$query = "SELECT o1.title role,o2.title container FROM object_data o1 ".
			"JOIN rbac_fa fa ON o1.obj_id = rol_id ".
			"JOIN tree t1 ON fa.parent =  t1.child ".
			"JOIN object_reference obr ON ref_id = t1.parent ".
			"JOIN object_data o2 ON obr.obj_id = o2.obj_id ".
			"WHERE o1.type = 'role' ".
			"AND assign = 'y' ".
			"AND ".$ilDB->like('o1.title','text','%'.$a_str.'%')." ".
			"AND fa.parent != 8 ".
			"ORDER BY role,container";
			
		$res = $ilDB->query($query);
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result->response->results[$counter] = new stdClass();
			$result->response->results[$counter]->role = $row->role;
			$result->response->results[$counter]->container = $row->container;
			++$counter;
		}

		if($counter == 0)
		{
			return self::getListByObject($a_str);
		}
		return ilJsonUtil::encode($result);
	}
	
	/**
	 * Get list of roles assigned to an object
	 * @return 
	 * @param object $result
	 */
	public static function getListByObject($a_str)
	{
		global $rbacreview,$ilDB;
		
		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		$result = new stdClass();
		$result->response = new stdClass();
		$result->response->results = array();

		if(strpos($a_str,'@') !== 0)
		{
			return ilJsonUtil::encode($result);
		}
		
		$a_str = substr($a_str,1);
		
		$ilDB->setLimit(100);
		$query = "SELECT ref_id, title FROM object_data ode ".
			"JOIN object_reference ore ON ode.obj_id = ore.obj_id ".
			"WHERE ".$ilDB->like('title', 'text',$a_str.'%').' '.
			'ORDER BY title';
		$res = $ilDB->query($query);
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rolf = $rbacreview->getRoleFolderIdOfObject($row->ref_id);
			if($rolf)
			{
				foreach($rbacreview->getRolesOfRoleFolder($rolf,false) as $rol_id)
				{
					$result->response->results[$counter] = new stdClass();
					$result->response->results[$counter]->role = ilObject::_lookupTitle($rol_id);
					$result->response->results[$counter]->container = $row->title;
					++$counter;
				}
			}
		}
		return ilJsonUtil::encode($result);
	}
}
?>
