<#1>
<?php

if(!$ilDB->tableExists('il_meta_oer_stat'))
{
	$ilDB->createTable('il_meta_oer_stat', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
		),
		'href_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'blocked' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
}
?>
