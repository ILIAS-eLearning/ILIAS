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
	
	public static function create($role, $stamp)
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
			'role' => array('integer', $role)
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
	
	public static function getCodesData($order_field, $order_direction, $offset, $limit, $filter_code, $filter_role, $filter_generated)
	{
		global $ilDB;
		
		// filter
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
			$where[] ="generated = ".$ilDB->quote($filter_generated, "text");
		}
		if(sizeof($where))
		{
			$where = " WHERE ".implode(" AND ", $where);
		}
		else
		{
			$where = "";
		}

		// count query
		$set = $ilDB->query("SELECT COUNT(*) AS cnt FROM ".self::DB_TABLE.$where);
		$cnt = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt = $rec["cnt"];
		}
		
		// set query
		$ilDB->setLimit((int)$limit, (int)$offset);
		$set = $ilDB->query("SELECT * FROM ".self::DB_TABLE.$where." ORDER BY ".$order_field." ".$order_direction);
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

}
?>