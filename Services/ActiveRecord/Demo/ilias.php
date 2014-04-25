<?php

chdir(strstr(__FILE__, 'Customizing', true));
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


global $ilCtrl, $tpl;
/**
 * @var $ilCtrl ilCtrl
 * @var $tpl    ilTemplate
 */
$tpl->getStandardTemplate();
$tpl->setVariable('BASE', '/');

require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Demo/StorageRecord/class.arStorageRecordGUI.php');

$arTestRecordGUI = new arStorageRecordGUI();
$arTestRecordGUI->executeCommand();
$tpl->show();

?>


