<?php
// IMPORTANT: Inform the lead developer, if you want to add any steps here.
//
// This is the hotfix file for ILIAS 5.0.x DB fixes
// This file should be used, if bugfixes need DB changes, but the
// main db update script cannot be used anymore, since it is
// impossible to merge the changes with the trunk.
//
// IMPORTANT: The fixes done here must ALSO BE reflected in the trunk.
// The trunk needs to work in both cases !!!
// 1. If the hotfixes have been applied.
// 2. If the hotfixes have not been applied.
?>
<#1>
<?php
if(!$ilDB->tableColumnExists('notification_osd', 'visible_for'))
{
	$ilDB->addTableColumn('notification_osd', 'visible_for', array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0)
	);
}
?>