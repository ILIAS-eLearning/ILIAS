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
	
if(!$ilDB->tableExists("rep_robj_wbe")) {
	$ilDB->createTable("rep_robj_wbe", $fields);
	$ilDB->addPrimaryKey("rep_robj_wbe", array("id"));
}
?>