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
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php

$query = "
	UPDATE tst_rnd_quest_set_qpls SET pool_title = (
		COALESCE(
			(SELECT title FROM object_data WHERE obj_id = pool_fi), %s 
		)
	) WHERE pool_title IS NULL OR pool_title = %s
";

$ilDB->manipulateF($query, array('text', 'text'), array('*** unknown/deleted ***', ''));

?>
<#3>
<?php

if( !$ilDB->tableColumnExists('tst_tests', 'broken'))
{
	$ilDB->addTableColumn('tst_tests', 'broken',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => null
		)
	);

	$ilDB->queryF("UPDATE tst_tests SET broken = %s", array('integer'), array(0));
}

?>
<#4>
<?php
$ilDB->manipulate("UPDATE style_data SET ".
	" uptodate = ".$ilDB->quote(0, "integer")
	);
?>
<#5>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php
$ilDB->manipulate("UPDATE tst_active SET last_finished_pass = (tries - 1) WHERE last_finished_pass IS NULL");
?>
