<#1>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('bdga', 'Badge Settings');

?>
<#2>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#3>
<?php

if(!$ilDB->tableExists('badge_badge'))
{
	$ilDB->createTable('badge_badge', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'active' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),		
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'descr' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false
		),
		'conf' => array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false
		)
	));	
	$ilDB->addPrimaryKey('badge_badge',array('id'));
	$ilDB->createSequence('badge_badge');
}

?>