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
<#3>
	<?php
	if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_status'))
	{
		$ilDB->addTableColumn('svy_svy', 'tutor_res_status', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 1
		));
	}
	if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_reci'))
	{
		$ilDB->addTableColumn('svy_svy', 'tutor_res_reci', array(
			'type' => 'text',
			'length'  => 2000,
			'notnull' => false,
			'fixed' => false
		));
	}
	?>
<#4>
<?php
	if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_cron'))
	{
		$ilDB->addTableColumn('svy_svy', 'tutor_res_cron', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 1
		));
	}
?>