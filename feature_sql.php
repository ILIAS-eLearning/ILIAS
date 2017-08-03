<?php
require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

global $ilDB;

if(!$ilDB->tableColumnExists('usr_data', 'second_email'))
{
	$ilDB->addTableColumn('usr_data', 'second_email', 
		array('type' => 'text',
		      'length' => 80,
		      'notnull' => false
		));
}


if(!$ilDB->tableColumnExists('mail_options', 'mail_address_option'))
{
	$ilDB->addTableColumn('mail_options', 'mail_address_option',
		array('type' => 'integer',
		      'length' => 1,
		      'notnull' => true,
		      'default' => 3
		));
}

// 	$ilCtrlStructureReader->getStructure();
