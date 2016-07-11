<#1>
<?php
	$fields =
		array(
			'id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'year' => array(
				"type" => "integer",
				"length" => 8,
				"notnull" => true
			),
			'created_ts' => array(
				"type" => "integer",
				"length" => 8,
				"notnull" => true
			)
		);

	if(!$ilDB->tableExists("rep_robj_rdbv")) {
		$ilDB->createTable("rep_robj_rdbv", $fields);
		$ilDB->addPrimaryKey("rep_robj_rdbv", array("id"));
	}
?>