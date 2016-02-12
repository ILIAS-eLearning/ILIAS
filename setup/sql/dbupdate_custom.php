<#1>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('bdga', 'Badge Settings');

?>
<#2>
<?php

$ilCtrlStructureReader->getStructure();

?>