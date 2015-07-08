<#1>
<?php

if (!$ilDB->tableColumnExists('adv_md_record_objs', 'optional'))
{
	$ilDB->addTableColumn('adv_md_record_objs', 'optional', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0
	));
}
	
?>