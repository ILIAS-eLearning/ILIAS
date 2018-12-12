<#5432>
<?php
if ($ilDB->tableExists('license_data')) {
	$ilDB->dropTable('license_data');
}
?>
<#5433>
<?php
$ilDB->manipulateF(
	'DELETE FROM settings WHERE module = %s',
	['text'],
	['license']
);
?>
<#5434>
<?php
$ilCtrlStructureReader->getStructure();
?>