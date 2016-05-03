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

<#2>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT bi.id,bi.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_rbi bi ON bi.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>