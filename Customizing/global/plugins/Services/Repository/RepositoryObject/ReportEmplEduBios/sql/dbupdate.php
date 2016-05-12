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
		'video_link' => array(
			"type" => "text",
			"length" => 400,
			"notnull" => false
		)

	);
 
$ilDB->createTable("rep_robj_reeb", $fields);
$ilDB->addPrimaryKey("rep_robj_reeb", array("id"));
?>

<#2>
<?php
$query = "INSERT INTO rep_master_data (id,is_online)"
		."	SELECT bi.id,bi.is_online FROM rep_master_data md"
		."		RIGHT JOIN rep_robj_reeb bi ON bi.id = md.id "
		."	WHERE md.id IS NULL";
$ilDB->manipulate($query);
?>

<#3>
<?php
	if(!$ilDB->tableColumnExists("rep_robj_reeb", "truncate_orgu_filter")) {
		$ilDB->addTableColumn('rep_robj_reeb', 'truncate_orgu_filter', array(
						'type' => 'integer',
						'length' => 1,
						'notnull' => false
						));
	}
?>