<?php

chdir('../..');

include_once './include/inc.header.php';

// -----------------------------------------------------------------------------

if( !$ilDB->tableColumnExists('tst_active', 'last_pmode') )
{
	$ilDB->addTableColumn('tst_active', 'last_pmode', array(
		'type' => 'text',
		'length' => 16,
		'notnull' => false,
		'default' => null
	));
}

// -----------------------------------------------------------------------------

if( !$ilDB->tableColumnExists('tst_solutions', 'authorized') )
{
	$ilDB->addTableColumn('tst_solutions', 'authorized', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => 1
	));

	$ilDB->queryF("UPDATE tst_solutions SET authorized = %s", array('integer'), array(1));
}

// -----------------------------------------------------------------------------

echo '[ OK ]';