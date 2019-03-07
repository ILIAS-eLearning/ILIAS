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
	$ilDB = $DIC->database();
	
	if( !$ilDB->tableColumnExists('qpl_questions', 'lifecycle') )
	{
		$ilDB->addTableColumn('qpl_questions', 'lifecycle', array(
			'type' => 'text',
			'length' => 16,
			'notnull' => false,
			'default' => 'draft'
		));
		
		$ilDB->queryF('UPDATE qpl_questions SET lifecycle = %s', array('text'), array('draft'));
	}
	
	if( !$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'lifecycle_filter'))
	{
		$ilDB->addTableColumn('tst_rnd_quest_set_qpls', 'lifecycle_filter',
			array('type' => 'text', 'length' => 250, 'notnull'	=> false, 'default'	=> null)
		);
	}
	
	echo '[ OK ]';
}
catch(ilException $e)
{
	echo "<pre>{$e}</pre>";
}
