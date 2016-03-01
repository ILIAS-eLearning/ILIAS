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
		)
	);
 
$ilDB->createTable("rep_robj_roa", $fields);
$ilDB->addPrimaryKey("rep_robj_roa", array("id"));
?>

<#2>
<?php
		$ilDB->addTableColumn('rep_robj_roa', 'is_local', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
?>

<#3>
<?php
		$ilDB->addTableColumn('rep_robj_roa', 'all_orgus_filter', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
?>