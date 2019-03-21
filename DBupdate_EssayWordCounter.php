<?php /* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    // use $ilDB for doing updates
    
	if( !$ilDB->tableColumnExists('qpl_qst_essay', 'word_cnt_enabled') )
	{
		$ilDB->addTableColumn('qpl_qst_essay', 'word_cnt_enabled', array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => false,
			'default' => 0
		));
	}
	
	echo '[ OK ]';
}
catch(ilException $e)
{
	echo "<pre>{$e}</pre>";
}
