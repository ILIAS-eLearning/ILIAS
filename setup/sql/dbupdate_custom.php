<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
	if (!$ilDB->tableColumnExists('svy_svy', 'reminder_tmpl'))
	{
		$ilDB->addTableColumn('svy_svy', 'reminder_tmpl', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>