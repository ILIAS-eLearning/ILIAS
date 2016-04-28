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
		'annual_norm_training' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'annual_norm_operation' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'annual_norm_office' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	);
 
$ilDB->createTable("rep_robj_rtw", $fields);
$ilDB->addPrimaryKey("rep_robj_rtw", array("id"));
?>

<#2>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT bi.id,bi.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_rtw bi ON bi.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>