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

<#3>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT bi.id,bi.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_rds bi ON bi.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>

<#4>
<?php
if(!$ilDB->tableColumnExists('rep_robj_rds', 'dbv_report_ref')) {
	$ilDB->addTableColumn('rep_robj_rds', 'dbv_report_ref', array(
			"type" => "integer",
			"length" => 4,
			"notnull" => true,
			"default" => 0
		));
}
?>