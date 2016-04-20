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
			'type' => 'text',
			'length' => 400,
			'notnull' => true
		));
if(!$ilDB->tableExists("rep_master_data")) {
	$ilDB->createTable("rep_master_data", $fields);
	$ilDB->addPrimaryKey("rep_master_data", array("id"));
}
?>
