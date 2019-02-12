<?php /* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'include/inc.header.php';

/* @var ilAccess $ilAccess */
if( !$ilAccess->checkAccess('read', '', SYSTEM_FOLDER_ID) )
{
	die('administrative privileges only!');
}

/* @var \ILIAS\DI\Container $DIC */
try
{
	// use $DIC->database() for doing updates
	
	if( !$DIC->database()->tableColumnExists('tst_tests', 'block_after_passed') )
	{
		$DIC->database()->addTableColumn('tst_tests', 'block_after_passed', array(
			'type' => 'integer',
			'notnull' => false,
			'length' => 1,
			'default' => 0
		));
	}
}
catch(ilException $e)
{
	echo "<pre>{$e}</pre>";
}
