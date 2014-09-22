<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilAccountCode
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilRegistrationSettingsGUI.php 23797 2010-05-07 15:54:03Z jluetzen $
*
* @ingroup ServicesUser
*/
class ilAccountCode
{
	const DB_TABLE = 'usr_account_codes';
	const CODE_LENGTH = 10;
	
	public static function create($valid_until, $stamp)
	{
		global $ilDB;
		
		$id = $ilDB->nextId(self::DB_TABLE);
		
		// create unique code
		$found = true;
		while ($found)
		{
			$code = self::generateRandomCode();
 			$chk = $ilDB->queryF("SELECT code_id FROM ".self::DB_TABLE." WHERE code = %s", array("text"), array($code));
			$found = (bool)$ilDB->numRows($chk);
		}
		
		$data = array(
			'code_id' => array('integer', $id),
			'code' => array('text', $code),
			'generated' => array('integer', $stamp),
			'valid_until' => array('text', $valid_until)
			);

		$ilDB->insert(self::DB_TABLE, $data);
		return $id;
	}
	
	protected static function generateRandomCode()
	{
		// missing : 01iloO
		$map = "23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
		
		$code = "";
		$max = strlen($map)-1;
		for($loop = 1; $loop <= self::CODE_LENGTH; $loop++)
		{
		  $code .= $map[mt_rand(0, $max)];
		}
		return $code;
	}
	
	public static function getCodesData($order_field, $order_direction, $offset, $limit, $filter_code, $filter_valid_until, $filter_generated)
	{
		global $ilDB;
		
		// filter
		$where = self::filterToSQL($filter_code, $filter_valid_until, $filter_generated);

		// count query
		$set = $ilDB->query("SELECT COUNT(*) AS cnt FROM ".self::DB_TABLE.$where);
		$cnt = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt = $rec["cnt"];
		}
		
		$sql = "SELECT * FROM ".self::DB_TABLE.$where;
		if($order_field)
		{
			$sql .= " ORDER BY ".$order_field." ".$order_direction;
		}
		
		// set query
		$ilDB->setLimit((int)$limit, (int)$offset);
		$set = $ilDB->query($sql);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}
		return array("cnt" => $cnt, "set" => $result);
	}
	
	public static function loadCodesByIds(array $ids)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM ".self::DB_TABLE." WHERE ".$ilDB->in("code_id", $ids, false, "integer"));
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}
		return $result;
	}
	
	public static function deleteCodes(array $ids)
	{
		global $ilDB;

		if(sizeof($ids))
		{
			return $ilDB->manipulate("DELETE FROM ".self::DB_TABLE." WHERE ".$ilDB->in("code_id", $ids, false, "integer"));
		}
		return false;
	}
	
	public static function getGenerationDates()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT DISTINCT(generated) AS generated FROM ".self::DB_TABLE." ORDER BY generated");
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec["generated"];
		}
		return $result;
	}
	
	protected static function filterToSQL($filter_code, $filter_valid_until, $filter_generated)
	{
		global $ilDB;

		$where = array();
		if($filter_code)
		{
			$where[] = $ilDB->like("code", "text", "%".$filter_code."%");
		}
		if($filter_valid_until)
		{
			$where[] ="valid_until = ".$ilDB->quote($filter_valid_until, "text");
		}
		if($filter_generated)
		{
			$where[] ="generated = ".$ilDB->quote($filter_generated, "text");
		}
		if(sizeof($where))
		{
			return " WHERE ".implode(" AND ", $where);
		}
		else
		{
			return "";
		}
	}
	
	public static function getCodesForExport($filter_code, $filter_valid_until, $filter_generated)
	{
		global $ilDB;

		// filter
		$where = self::filterToSQL($filter_code, $filter_valid_until, $filter_generated);

		// set query
		$set = $ilDB->query("SELECT code FROM ".self::DB_TABLE.$where." ORDER BY code_id");
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec["code"];
		}
		return $result;
	}
	
	public static function isUnusedCode($code)
	{
		global $ilDB;
		
		include_once './Services/Registration/classes/class.ilRegistrationCode.php';
		return ilRegistrationCode::isUnusedCode($code);
		
		
		$set = $ilDB->query("SELECT used FROM ".self::DB_TABLE." WHERE code = ".$ilDB->quote($code, "text"));
		$set = $ilDB->fetchAssoc($set);
		if($set && !$set["used"])
		{
			return true;
		}
		return false;
	}
	
	public static function useCode($code)
	{
		global $ilDB;
		
		include_once './Services/Registration/classes/class.ilRegistrationCode.php';
		return (bool) ilRegistrationCode::useCode($code);

		return (bool)$ilDB->update(self::DB_TABLE, array("used"=>array("timestamp", time())), array("code"=>array("text", $code)));
	}

	public static function getCodeValidUntil($code)
    {
		global $ilDB;
		
		include_once './Services/Registration/classes/class.ilRegistrationCode.php';
		$code_data = ilRegistrationCode::getCodeData($code);
		
		if($code_data["alimit"])
		{
			switch($code_data["alimit"])
			{
				case "absolute":
					return $code_data['alimitdt'];
			}
		}
		return "0";

		$set = $ilDB->query("SELECT valid_until FROM ".self::DB_TABLE." WHERE code = ".$ilDB->quote($code, "text"));
		$row = $ilDB->fetchAssoc($set);
		if(isset($row["valid_until"]))
		{
			return $row["valid_until"];
		}
	}
	
	public static function applyRoleAssignments(ilObjUser $user, $code)
	{
		include_once './Services/Registration/classes/class.ilRegistrationCode.php';
		
		$grole = ilRegistrationCode::getCodeRole($code);
		if($grole)
		{
			$GLOBALS['rbacadmin']->assignUser($grole,$user->getId());
		}
		$code_data = ilRegistrationCode::getCodeData($code);
		if($code_data["role_local"])
		{
			$code_local_roles = explode(";", $code_data["role_local"]);
			foreach((array) $code_local_roles as $role_id)
			{
				$GLOBALS['rbacadmin']->assignUser($role_id,$user->getId());
				
				// patch to remove for 45 due to mantis 21953
				$role_obj = $GLOBALS['rbacreview']->getObjectOfRole($role_id);
				switch(ilObject::_lookupType($role_obj))
				{
					case 'crs':
					case 'grp':
						$role_refs = ilObject::_getAllReferences($role_obj);
						$role_ref = end($role_refs);
						ilObjUser::_addDesktopItem($user->getId(),$role_ref,ilObject::_lookupType($role_obj));
						break;
				}
			}
		}
		return true;
	}
	
	public static function applyAccessLimits(ilObjUser $user, $code)
	{
		include_once './Services/Registration/classes/class.ilRegistrationCode.php';
		$code_data = ilRegistrationCode::getCodeData($code);
		
		if($code_data["alimit"])
		{
			switch($code_data["alimit"])
			{
				case "absolute":
					$end = new ilDateTime($code_data['alimitdt'],IL_CAL_DATE);
					$user->setTimeLimitFrom(time());
					$user->setTimeLimitUntil($end->get(IL_CAL_UNIX));
					$user->setTimeLimitUnlimited(0);
					break;
						
				case "relative":					

					$rel = unserialize($code_data["alimitdt"]);
					
					include_once './Services/Calendar/classes/class.ilDateTime.php';
					$end = new ilDateTime(time(),IL_CAL_UNIX);
					$end->increment(IL_CAL_YEAR, $rel['y']);
					$end->increment(IL_CAL_MONTH, $rel['m']);
					$end->increment(IL_CAL_DAY, $rel['d']);
					
					$user->setTimeLimitFrom(time());
					$user->setTimeLimitUntil($end->get(IL_CAL_UNIX));
					$user->setTimeLimitUnlimited(0);
					break;
				
				case 'unlimited':
					$user->setTimeLimitUnlimited(1);
					break;
					
			}
		}
		else
		{
			$user->setTimeLimitUnlimited(1);
		}
		
	}
}

?>