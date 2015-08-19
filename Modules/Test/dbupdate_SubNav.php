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

echo '[ OK ]';