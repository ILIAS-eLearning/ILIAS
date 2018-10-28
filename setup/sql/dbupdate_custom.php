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


