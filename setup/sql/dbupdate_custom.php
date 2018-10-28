<#1>
<?php
if(!$ilDB->tableColumnExists('crs_settings','timing_mode'))
{
	$ilDB->addTableColumn(
		'crs_settings',
		'timing_mode',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('crs_items','suggestion_start_rel'))
{
	$ilDB->addTableColumn(
		'crs_items',
		'suggestion_start_rel',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		));

}
?>
<#3>
<?php

if(!$ilDB->tableColumnExists('crs_items','suggestion_end_rel'))
{
	$ilDB->addTableColumn(
		'crs_items',
		'suggestion_end_rel',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		));

}
?>
<#4>
<?php

if(!$ilDB->tableColumnExists('crs_items','earliest_start_rel'))
{
	$ilDB->addTableColumn(
		'crs_items',
		'earliest_start_rel',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		));

}
?>
<#5>
<?php

if(!$ilDB->tableColumnExists('crs_items','latest_end_rel'))
{
	$ilDB->addTableColumn(
		'crs_items',
		'latest_end_rel',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		));

}
?>

<#6>
<?php

if($ilDB->tableColumnExists('crs_items','earliest_start'))
{
	$ilDB->dropTableColumn('crs_items','earliest_start');
}
if($ilDB->tableColumnExists('crs_items','latest_end'))
{
	$ilDB->dropTableColumn('crs_items','latest_end');
}
if($ilDB->tableColumnExists('crs_items','earliest_start_rel'))
{
	$ilDB->dropTableColumn('crs_items','earliest_start_rel');
}
if($ilDB->tableColumnExists('crs_items','latest_end_rel'))
{
	$ilDB->dropTableColumn('crs_items','latest_end_rel');
}
?>
<#7>
<?php
if(!$ilDB->tableExists('crs_timings_user'))
{
	$ilDB->createTable('crs_timings_user', array(
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sstart' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ssend' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('crs_timings_user', array('ref_id', 'usr_id'));
}
?>
