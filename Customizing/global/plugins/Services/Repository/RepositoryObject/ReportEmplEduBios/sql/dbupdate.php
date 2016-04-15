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