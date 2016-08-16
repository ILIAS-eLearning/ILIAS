<?php

// ---------------------------------------------------------------------------------------------------------------------

include_once './include/inc.header.php';

if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	die('Sorry, this script requires administrative privileges!');
}

// ---------------------------------------------------------------------------------------------------------------------

if(!$ilDB->tableColumnExists('tst_tests','pass_waiting'))
{
	$ilDB->addTableColumn('tst_tests', 'pass_waiting', array(
			'type'    => 'text',
			'length'  => 15,
			'notnull' => false,
			'default' => null)
	);
}

// ---------------------------------------------------------------------------------------------------------------------

echo "Thank you for updating the database successfully ;-) Yours Developer -.-";
