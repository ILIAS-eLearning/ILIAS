<#1>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
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
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);

?>

<#3>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addCustomRBACOperation(
		'manage_materials',
		'Manage Materials',
		'object',
		6500
);
?>
<#4>
<?php
// Hallo
?>
<#5>
<?php
// Hallo 2
?>
<#6>
<?php
// Hallo 3
?>

<#7>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId('sess');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_materials');

if($tgt_ops_id && $type_id)
{
	ilDBUpdateNewObjectType::addRBACOperation($type_id, $tgt_ops_id);
}

?>
<#8>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$src_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
$tgt_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_materials');
ilDBUpdateNewObjectType::cloneOperation('sess', $src_ops_id, $tgt_ops_id);
?>


