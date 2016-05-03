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
 
$ilDB->createTable("rep_robj_rea", $fields);
$ilDB->addPrimaryKey("rep_robj_rea", array("id"));
?>

<#2>
<?php
	$field_data = array('type' => 'clob', 'notnull' => false, 'default' =>'');

	if(!$ilDB->tableColumnExists("rep_robj_rea", "video_link")) {
		$ilDB->addTableColumn("rep_robj_rea", "video_link", $field_data);
	}
?>

<#3>
<?php
	$field_data = array('type' => 'clob', 'notnull' => false, 'default' =>'');

	if(!$ilDB->tableColumnExists("rep_robj_rea", "pdf_link")) {
		$ilDB->addTableColumn("rep_robj_rea", "pdf_link", $field_data);
	}
?>

<#4>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT bi.id,bi.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_rea bi ON bi.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>