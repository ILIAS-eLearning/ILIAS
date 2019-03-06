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
	if( !$DIC->database()->tableColumnExists('qpl_questions', 'lifecycle') )
	{
		$DIC->database()->addTableColumn('qpl_questions', 'lifecycle', array(
			'type' => 'text',
			'length' => 16,
			'notnull' => false,
			'default' => 'draft'
		));
		
		$DIC->database()->queryF('UPDATE qpl_questions SET lifecycle = %s', array('text'), array('draft'));
	}
	
	echo '[ OK ]';
}
catch(ilException $e)
{
	echo "<pre>{$e}</pre>";
}
