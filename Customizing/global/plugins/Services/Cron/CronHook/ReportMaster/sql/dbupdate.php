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
			'notnull' => false
		));
if(!$ilDB->tableExists("rep_master_data")) {
	$ilDB->createTable("rep_master_data", $fields);
	$ilDB->addPrimaryKey("rep_master_data", array("id"));
}
?>

<#2>
<?php
	if (!$ilDB->tableColumnExists('rep_master_data', 'pdf_link')) {		
		$ilDB->addTableColumn('rep_master_data', 'pdf_link',array(
			'type' => 'text',
			'length' => 400,
			'notnull' => false
		));
	}

	if (!$ilDB->tableColumnExists('rep_master_data', 'tooltip_info')) {		
		$ilDB->addTableColumn('rep_master_data', 'tooltip_info',array(
			'type' => 'clob',
			'notnull' => false
		));
	}
?>