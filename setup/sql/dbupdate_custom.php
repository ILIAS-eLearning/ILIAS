<#1>
<?php
if($ilDB->tableColumnExists('svy_svy', 'mode_360'))
{
	$ilDB->renameTableColumn('svy_svy', 'mode_360', 'mode');
}
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('svy_svy', 'mode_self_eval_results'))
{
	$ilDB->addTableColumn(
		'svy_svy',
		'mode_self_eval_results',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>