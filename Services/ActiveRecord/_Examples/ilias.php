<?php

chdir(strstr(__FILE__, 'Customizing', true));
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


global $DIC;
$ilCtrl = $DIC['ilCtrl'];
$tpl = $DIC['tpl'];
/**
 * @var $ilCtrl ilCtrl
 * @var $tpl    ilTemplate
 */
$tpl->getStandardTemplate();
$tpl->setVariable('BASE', '/');

require_once('./Services/ActiveRecord/_Examples/StorageRecord/class.arStorageRecordGUI.php');

$arTestRecordGUI = new arStorageRecordGUI();
$arTestRecordGUI->executeCommand();
$tpl->show();

?>


