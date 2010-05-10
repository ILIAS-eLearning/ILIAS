<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilRegistrationCode
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id: class.ilRegistrationSettingsGUI.php 23797 2010-05-07 15:54:03Z jluetzen $
*
* @ilCtrl_Calls ilRegistrationCode:
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
}
?>