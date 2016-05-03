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
		'admin_mode' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false
		)
	);
 
$ilDB->createTable("rep_robj_rcp", $fields);
$ilDB->addPrimaryKey("rep_robj_rcp", array("id"));
?>

<#2>
<?php
$a = true;
?>


<#3>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT bi.id,bi.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_rcp bi ON bi.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>