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
<#4>
<?php

if(!$ilDB->tableExists('badge_image_template'))
{
	$ilDB->createTable('badge_image_template', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),		
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'image' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		)
	));	
	$ilDB->addPrimaryKey('badge_image_template',array('id'));
	$ilDB->createSequence('badge_image_template');
}

?>
<#5>
<?php

if(!$ilDB->tableColumnExists('badge_badge','image')) 
{
    $ilDB->addTableColumn(
        'badge_badge',
        'image',
        array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false)	
        );
}

?>
<#6>
<?php

if(!$ilDB->tableExists('badge_image_templ_type'))
{
	$ilDB->createTable('badge_image_templ_type', array(
		'tmpl_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type_id' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => true,
			'default' => ""
		)
	));	
	$ilDB->addPrimaryKey('badge_image_templ_type',array('tmpl_id', 'type_id'));
}

?>
<#7>
<?php

if(!$ilDB->tableExists('badge_user_badge'))
{
	$ilDB->createTable('badge_user_badge', array(
		'badge_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'awarded_by' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => false
		)
	));	
	$ilDB->addPrimaryKey('badge_user_badge',array('badge_id', 'user_id'));
}

?>
<#8>
<?php

if(!$ilDB->tableColumnExists('badge_badge','valid')) 
{
    $ilDB->addTableColumn(
        'badge_badge',
        'valid',
        array(            
			'type' => 'text',
			'length' => 255,
			'notnull' => false)	
        );
}

?>
<#9>
<?php

if(!$ilDB->tableExists('object_data_del'))
{
	$ilDB->createTable('object_data_del', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),		
	));	
	$ilDB->addPrimaryKey('object_data_del',array('obj_id'));
}

?>
<#10>
<?php

if(!$ilDB->tableColumnExists('object_data_del','type')) 
{
    $ilDB->addTableColumn(
        'object_data_del',
        'type',
        array(            
			'type' => 'text',
			'length' => 4,
			'fixed' => true,
			'notnull' => false)	
        );
}

?>
<#11>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#12>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#13>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#14>
<?php

if(!$ilDB->tableColumnExists('badge_badge','crit')) 
{
    $ilDB->addTableColumn(
        'badge_badge',
        'crit',
        array(
			'type' => 'text',
			'length' => 4000,
			'notnull' => false
		)
	);
}

?>
<#15>
<?php

$ilCtrlStructureReader->getStructure();

?>
<#16>
<?php

$ilCtrlStructureReader->getStructure();

?>