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
if(!$ilDB->tableExists("rep_robj_astd")) {
	$ilDB->createTable("rep_robj_astd", $fields);
	$ilDB->addPrimaryKey("rep_robj_astd", array("id"));
}
?>

<#2>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT atd.id,atd.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_astd atd ON atd.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>