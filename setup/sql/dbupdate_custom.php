<#1>
<?php
if(!$ilDB->tableColumnExists('grp_settings', 'grp_start'))
{
		$ilDB->addTableColumn('grp_settings', 'grp_start', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
}
if(!$ilDB->tableColumnExists('grp_settings', 'grp_end'))
{
		$ilDB->addTableColumn('grp_settings', 'grp_end', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
}
?>	
