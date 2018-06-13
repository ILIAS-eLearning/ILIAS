<?php /* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir('../../');
include_once 'include/inc.header.php';

/* @var ilAccess $ilAccess */
if( !$ilAccess->checkAccess('read', '', SYSTEM_FOLDER_ID) )
{
	die('administrative privileges only!');
}

/* @var \ILIAS\DI\Container $DIC */
try
{
	if( !$DIC->database()->tableColumnExists('tst_tests', 'follow_qst_answer_fixation') )
	{
		$DIC->database()->addTableColumn('tst_tests', 'follow_qst_answer_fixation', array(
			'type' => 'integer', 'notnull' => false, 'length' => 1, 'default' => 0		
		));
		
		$DIC->database()->manipulateF(
			'UPDATE tst_tests SET follow_qst_answer_fixation = %s', array('integer'), array(0)
		);
	}
}
catch(ilException $e)
{
	
}
