<#1>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addAdminNode('wbdv', 'WebDAV');
$ilCtrlStructureReader->getStructure();
// END MME
?>
