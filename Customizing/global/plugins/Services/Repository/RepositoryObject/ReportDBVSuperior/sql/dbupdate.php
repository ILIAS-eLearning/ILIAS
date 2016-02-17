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
 
$ilDB->createTable("rep_robj_rds", $fields);
$ilDB->addPrimaryKey("rep_robj_rds", array("id"));
?>

<#2>
<?php
if(!$ilDB->tableColumnExists('rep_robj_rds', 'year')) {
	$ilDB->addTableColumn('rep_robj_rds', 'year', array(
			"type" => "integer",
			"length" => 8,
			"notnull" => true,
			"default" => 2015
		));
}
?>