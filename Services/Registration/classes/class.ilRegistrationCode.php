<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilRegistrationCode
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id: class.ilRegistrationSettingsGUI.php 23797 2010-05-07 15:54:03Z jluetzen $
*
* @ingroup ServicesRegistration
*/
class ilRegistrationCode
{
	const DB_TABLE = 'reg_registration_codes';
	const CODE_LENGTH = 10;
	
	public static function create($role, $stamp, $local_roles, $limit, $limit_date, $reg_type, $ext_type)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$id = $ilDB->nextId(self::DB_TABLE);
		
		// create unique code
		$found = true;
		while ($found)
		{
			$code = self::generateRandomCode();
 			$chk = $ilDB->queryF("SELECT code_id FROM ".self::DB_TABLE." WHERE code = %s", array("text"), array($code));
			$found = (bool)$ilDB->numRows($chk);
		}
		
		if(is_array($local_roles))
		{
			$local_roles = implode(";", $local_roles);
		}
		if($limit == "relative" && is_array($limit_date))
		{
			$limit_date = serialize($limit_date);
		}
		
		$data = array(
			'code_id' => array('integer', $id),
			'code' => array('text', $code),
			'generated_on' => array('integer', $stamp),
			'role' => array('integer', $role),
			'role_local' => array('text', $local_roles),
			'alimit' => array('text', $limit),
			'alimitdt' => array('text', $limit_date),
			'reg_enabled' => array('integer',$reg_type),
			'ext_enabled' => array('integer',$ext_type)
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
	
	public static function getCodesData($order_field, $order_direction, $offset, $limit, $filter_code, $filter_role, $filter_generated, $filter_access_limitation)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		// filter
		$where = self::filterToSQL($filter_code, $filter_role, $filter_generated, $filter_access_limitation);

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
			if($order_field == 'generated')
			{
				$order_field = 'generated_on';
			}
			$sql .= " ORDER BY ".$order_field." ".$order_direction;
		}
		
		// set query
		$ilDB->setLimit((int)$limit, (int)$offset);
		$set = $ilDB->query($sql);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$rec['generated'] = $rec['generated_on'];
			$result[] = $rec;
		}
		return array("cnt" => $cnt, "set" => $result);
	}
	
	public static function loadCodesByIds(array $ids)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

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
		global $DIC;

		$ilDB = $DIC['ilDB'];

		if(sizeof($ids))
		{
			return $ilDB->manipulate("DELETE FROM ".self::DB_TABLE." WHERE ".$ilDB->in("code_id", $ids, false, "integer"));
		}
		return false;
	}
	
	public static function getGenerationDates()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$set = $ilDB->query("SELECT DISTINCT(generated_on) genr FROM ".self::DB_TABLE." ORDER BY genr");
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec["genr"];
		}
		return $result;
	}
	
	protected static function filterToSQL($filter_code, $filter_role, $filter_generated, $filter_access_limitation)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$where = array();
		if($filter_code)
		{
			$where[] = $ilDB->like("code", "text", "%".$filter_code."%");
		}
		if($filter_role)
		{
			$where[] ="role = ".$ilDB->quote($filter_role, "integer");
		}
		if($filter_generated)
		{
			$where[] ="generated_on = ".$ilDB->quote($filter_generated, "text");
		}
		if($filter_access_limitation)
		{
			$where[] ="alimit = ".$ilDB->quote($filter_access_limitation, "text");
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
	
	public static function getCodesForExport($filter_code, $filter_role, $filter_generated, $filter_access_limitation)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		// filter
		$where = self::filterToSQL($filter_code, $filter_role, $filter_generated, $filter_access_limitation);

		// set query
		$set = $ilDB->query("SELECT code FROM ".self::DB_TABLE.$where." ORDER BY code_id");
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec["code"];
		}
		return $result;
	}
	
	/**
	 * Check if code has been used already
	 * @global type $ilDB
	 * @param type $code
	 * @return boolean
	 */
	public static function isUnusedCode($code)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$set = $ilDB->query("SELECT used FROM ".self::DB_TABLE." WHERE code = ".$ilDB->quote($code, "text"));
		$set = $ilDB->fetchAssoc($set);
		if($set && !$set["used"])
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Check if given code is a valid registration code
	 * @param string $a_code code
	 * @return bool
	 */
	public static function isValidRegistrationCode($a_code)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];
		
		$query = 'SELECT code_id FROM reg_registration_codes '.
			'WHERE used = '.$ilDB->quote(0,'integer').' '.
			'AND reg_enabled = '.$ilDB->quote(1,'integer').' '.
			'AND code = '.$ilDB->quote($a_code,'text');
		$res = $ilDB->query($query);
		
		return $res->numRows() ? true : false;
	}

	public static function useCode($code)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		return (bool)$ilDB->update(self::DB_TABLE, array("used"=>array("timestamp", time())), array("code"=>array("text", $code)));
	}

	public static function getCodeRole($code)
    {
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$set = $ilDB->query("SELECT role FROM ".self::DB_TABLE." WHERE code = ".$ilDB->quote($code, "text"));
		$row = $ilDB->fetchAssoc($set);
		if(isset($row["role"]))
		{
			return $row["role"];
		}
	}
	
	public static function getCodeData($code)
    {
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$set = $ilDB->query("SELECT role, role_local, alimit, alimitdt, reg_enabled, ext_enabled".
			" FROM ".self::DB_TABLE.
			" WHERE code = ".$ilDB->quote($code, "text"));
		$row = $ilDB->fetchAssoc($set);
		return $row;
	}
}
?>