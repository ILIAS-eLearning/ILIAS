<?php /* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../');
include_once 'include/inc.header.php';

/* @var ilAccess $ilAccess */
if( !$ilAccess->checkAccess('read', '', SYSTEM_FOLDER_ID) )
{
	die('administrative privileges only!');
}

/* @var \ILIAS\DI\Container $DIC */
$ilDB = $DIC->database();
try
{
	$setting = new ilSetting();
	
	if( !$setting->get('tst_score_rep_consts_cleaned', 0) )
	{
		$ilDB->queryF(
			"UPDATE tst_tests SET score_reporting = %s WHERE score_reporting = %s",
			array('integer', 'integer'), array(0, 4)
		);
		
		$setting->set('tst_score_rep_consts_cleaned', 1);
	}
}
catch(ilException $e)
{
	echo "<pre>{$e}</pre>";
}
