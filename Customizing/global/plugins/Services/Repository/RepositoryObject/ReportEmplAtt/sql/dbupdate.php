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
	if (!$ilDB->tableColumnExists('rep_robj_rea', 'title_info_link')) {
		$ilDB->addTableColumn('rep_robj_rea', 'title_info_link', array(
			"type" => "text",
			"length" => 400,
			"notnull" => false
		));
	}
?>