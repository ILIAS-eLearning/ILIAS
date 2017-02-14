<#1>
<?php
if($ilDB->tableExists("exc_assignment"))
{
	if(!$ilDB->tableColumnExists('exc_assignment','portfolio_template'))
	{
		$ilDB->addTableColumn("exc_assignment", "portfolio_template", array("type" => "integer", "length" => 4));
	}
	if(!$ilDB->tableColumnExists('exc_assignment','min_char_limit'))
	{
		$ilDB->addTableColumn("exc_assignment", "min_char_limit", array("type" => "integer", "length" => 4));
	}
	if(!$ilDB->tableColumnExists('exc_assignment','max_char_limit'))
	{
		$ilDB->addTableColumn("exc_assignment", "max_char_limit", array("type" => "integer", "length" => 4));
	}
}
?>
<#2>
<?php
if(!$ilDB->tableExists("exc_ass_file_order"))
{
	$fields = array(
		"id" => array(
			"type" => "integer",
			"length" => 4,
			"notnull" => true,
			"default" => 0
		),
		"assignment_id" => array(
			"type" => "integer",
			"length" => 4,
			"notnull" => true,
			"default" => 0
		),
		"filename" => array(
			"type" => "text",
			"length" => 150,
			"notnull" => true,
		),
		"order_nr" => array(
			"type" => "integer",
			"length" => 4,
			"notnull" => true,
			"default" => 0
		),
	);

	$ilDB->createTable("exc_ass_file_order", $fields);
	$ilDB->addPrimaryKey('exc_ass_file_order', array('id'));

	$ilDB->createSequence("exc_ass_file_order");
}
?>
