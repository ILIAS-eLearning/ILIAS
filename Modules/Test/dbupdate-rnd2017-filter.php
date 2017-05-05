<?php

while( !file_exists(getcwd().'/ilias.php') )
{
	chdir('../');
}

require_once './include/inc.header.php';

$rs = $GLOBALS['DIC'] ? $GLOBALS['DIC']['rbacsystem'] : $GLOBALS['rbacsystem'];
if(!$rs->checkAccess('visible,read', SYSTEM_FOLDER_ID))
{
	die('Sorry, this script requires administrative privileges!');
}

/* @var ilDB $db */
$db = $GLOBALS['DIC'] ? $GLOBALS['DIC']['ilDB'] : $GLOBALS['ilDB'];

// -----------------------------------------------------------------------------
/**
 * fau: taxFilter - extend the random question set condition to multiple taxonomy and node ids
 */
if( !$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'origin_tax_filter'))
{
	$ilDB->addTableColumn('tst_rnd_quest_set_qpls', 'origin_tax_filter',
		array('type' => 'text', 'length' => 4000, 'notnull'	=> false, 'default'	=> null)
	);
}
if( !$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'mapped_tax_filter'))
{
	$ilDB->addTableColumn('tst_rnd_quest_set_qpls', 'mapped_tax_filter',
		array('type' => 'text', 'length' => 4000, 'notnull'	=> false, 'default'	=> null)
	);
}

$query = "SELECT * FROM tst_rnd_quest_set_qpls WHERE origin_tax_fi IS NOT NULL OR mapped_tax_fi IS NOT NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchObject($result))
{
	if (!empty($row->origin_tax_fi))
	{
		$origin_tax_filter = serialize(array((int) $row->origin_tax_fi => array((int) $row->origin_node_fi)));
	}
	else
	{
		$origin_tax_filter = null;
	}
	
	if (!empty($row->mapped_tax_fi))
	{
		$mapped_tax_filter = serialize(array((int) $row->mapped_tax_fi => array((int) $row->mapped_node_fi)));
	}
	else
	{
		$mapped_tax_filter = null;
	}
	
	$update = "UPDATE tst_rnd_quest_set_qpls SET "
		. " origin_tax_fi = NULL, origin_node_fi = NULL, mapped_tax_fi = NULL, mapped_node_fi = NULL, "
		. " origin_tax_filter = " . $ilDB->quote($origin_tax_filter, 'text'). ", "
		. " mapped_tax_filter = " . $ilDB->quote($mapped_tax_filter, 'text')
		. " WHERE def_id = " . $ilDB->quote($row->def_id);
	
	$ilDB->manipulate($update);
}
// -----------------------------------------------------------------------------
/**
 * fau: typeFilter - extend the random question set condition to question type
 */
if( !$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'type_filter'))
{
	$ilDB->addTableColumn('tst_rnd_quest_set_qpls', 'type_filter',
		array('type' => 'text', 'length' => 250, 'notnull'	=> false, 'default'	=> null)
	);
}
// -----------------------------------------------------------------------------

echo '[ finished script ]';
exit;