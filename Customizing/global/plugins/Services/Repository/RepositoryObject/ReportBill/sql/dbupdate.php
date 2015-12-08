<#1>
<?php
$fields = 
	array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'is_online' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		),
		'report_mode' => array(
			'type' => 'integer',
			'length' => 2,
			'notnull' => true
		)
	);
 
$ilDB->createTable("rep_robj_rbi", $fields);
$ilDB->addPrimaryKey("rep_robj_rbi", array("id"));
?>
