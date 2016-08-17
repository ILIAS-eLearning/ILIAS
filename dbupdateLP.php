<?php

// ---------------------------------------------------------------------------------------------------------------------

include_once './include/inc.header.php';

if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	die('Sorry, this script requires administrative privileges!');
}

// ---------------------------------------------------------------------------------------------------------------------

if( !$ilDB->tableColumnExists('tst_active', 'last_started_pass') )
{
	$ilDB->addTableColumn('tst_active', 'last_started_pass', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false,
		'default' => null
	));
}
// ---------------------------------------------------------------------------------------------------------------------

echo "Thank you for updating the database successfully ;-) Yours Developer -.-";
