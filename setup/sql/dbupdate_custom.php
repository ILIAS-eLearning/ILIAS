<#1>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('crs');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if($type_id && $tgt_ops_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#2>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('grp');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');

if($type_id && $tgt_ops_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}
?>
<#2>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('crs', $src_ops_id, $tgt_ops_id);

?>
<#4>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_members');
ilDBUpdateNewObjectType::cloneOperation('grp', $src_ops_id, $tgt_ops_id);

?>