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
		'accomodation_cost' => array(
			'type' => 'float',
			'notnull' => true
		)
	);
 
$ilDB->createTable("rep_robj_astd", $fields);
$ilDB->addPrimaryKey("rep_robj_astd", array("id"));
?>
