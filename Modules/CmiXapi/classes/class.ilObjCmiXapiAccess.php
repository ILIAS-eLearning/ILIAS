<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiAccess
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapiAccess extends ilObjectAccess
{
	static function _getCommands()
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$commands = array
		(
			array(
				"permission" => "read",
				"cmd" => "infoScreen",
				"lang_var" => "infoScreen",
				"default" => true
			),
			array(
				'permission' => 'write',
				'cmd' => 'ilCmiXapiSettingsGUI::show',
				'lang_var' => ilObjCmiXapiGUI::TAB_ID_SETTINGS
			)
		);
		
		return $commands;
	}
	
	
	/**
	 * @param int $a_obj_id
	 * @return bool
	 */
	public static function _isOffline($a_obj_id)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$query = "
			SELECT		COUNT(*) cnt
			FROM		cmix_settings
			WHERE		obj_id = %s
			AND			offline_status = 1
		";
		
		$res = $DIC->database()->queryF( $query, array('integer'), array($a_obj_id) );
		$row = $DIC->database()->fetchAssoc($res);
		
		return (bool)$row['cnt'];
	}
	
}
