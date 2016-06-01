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
if(!$ilDB->tableExists("rep_robj_reb")) {
	$ilDB->createTable("rep_robj_reb", $fields);
	$ilDB->addPrimaryKey("rep_robj_reb", array("id"));
}
?>
