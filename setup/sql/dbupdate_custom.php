<#1>
<?php

if( !$ilDB->tableExists('adv_md_values_text') )
{
	$ilDB->renameTable('adv_md_values', 'adv_md_values_text');
}

?>
<#2>
<?php

if( !$ilDB->tableExists('adv_md_values_int') )
{
	$ilDB->createTable('adv_md_values_int', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#3>
<?php

if( !$ilDB->tableExists('adv_md_values_float') )
{
	$ilDB->createTable('adv_md_values_float', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'float',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#4>
<?php

if( !$ilDB->tableExists('adv_md_values_date') )
{
	$ilDB->createTable('adv_md_values_date', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'date',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#5>
<?php

if( !$ilDB->tableExists('adv_md_values_datetime') )
{
	$ilDB->createTable('adv_md_values_datetime', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'timestamp',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#6>
<?php

if( !$ilDB->tableExists('adv_md_values_location') )
{
	$ilDB->createTable('adv_md_values_location', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'loc_lat' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_long' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_zoom' => array(
			'type' => 'integer',			
			'length' => 1,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#7>
<?php

	if (!$ilDB->tableColumnExists('adv_md_values_location', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_location', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_datetime', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_datetime', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_date', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_date', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_float', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_float', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_int', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_int', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	
?>
<#8>
<?php

// #6/#7/#8

$ilDB->dropPrimaryKey('adv_md_values_int');
$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_float');
$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_date');
$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_datetime');
$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_location');
$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id', 'field_id'));

?>
<#9>
<?php
if(!$ilDB->tableExists('orgu_types')) {
    $fields = array (
        'id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'default_lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4, 'fixed' => false),
        'icon'    => array ('type' => 'text', 'length'  => 256, 'notnull' => false),
        'owner' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
        'create_date'  => array ('type' => 'timestamp'),
        'last_update' => array('type' => 'timestamp'),
    );
    $ilDB->createTable('orgu_types', $fields);
    $ilDB->addPrimaryKey('orgu_types', array('id'));
    $ilDB->createSequence('orgu_types');
}
?>
<#10>
<?php
if(!$ilDB->tableExists('orgu_data')) {
    $fields = array (
        'orgu_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'orgu_type_id'   => array ('type' => 'integer', 'notnull' => false, 'length' => 4),
    );
    $ilDB->createTable('orgu_data', $fields);
    $ilDB->addPrimaryKey('orgu_data', array('orgu_id'));
}
?>
<#11>
<?php
if(!$ilDB->tableExists('orgu_types_trans')) {
    $fields = array (
        'orgu_type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4),
        'member'    => array ('type' => 'text', 'length'  => 32, 'notnull' => true),
        'value' => array('type' => 'text', 'length' => 4000, 'notnull' => false),
    );
    $ilDB->createTable('orgu_types_trans', $fields);
    $ilDB->addPrimaryKey('orgu_types_trans', array('orgu_type_id', 'lang', 'member'));
}
?>
<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
if(!$ilDB->tableExists('orgu_types_adv_md_rec')) {
    $fields = array (
        'type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'rec_id'   => array ('type' => 'integer', 'notnull' => true, 'length' => 4),
    );
    $ilDB->createTable('orgu_types_adv_md_rec', $fields);
    $ilDB->addPrimaryKey('orgu_types_adv_md_rec', array('type_id', 'rec_id'));
}
?>
<#14>
<?php
$ilCtrlStructureReader->getStructure();
?>