<?php
// This is the hotfix file for ILIAS 5.3.x DB fixes
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
$ilDB->query("
UPDATE il_dcl_stloc1_value 
SET value = NULL 
WHERE value = '[]' 
       AND record_field_id IN (
               SELECT rf.id 
               FROM il_dcl_record_field rf 
               INNER JOIN il_dcl_field f ON f.id = rf.field_id 
               WHERE f.datatype_id = 14
       )
");
?>

