<#1>
<?php
$fields = 
	array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		)
	);
	
if(!$ilDB->tableExists("rep_robj_wbp")) {
	$ilDB->createTable("rep_robj_wbp", $fields);
	$ilDB->addPrimaryKey("rep_robj_wbp", array("id"));
}
?>