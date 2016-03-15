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
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php
if($ilDB->tableColumnExists('svy_times', 'first_question'))
{
	$ilDB->modifyTableColumn('svy_times', 'first_question', array(
											'type'	=> 'integer',
											'length'=> 4)
	);
}
?>
<#4>
<?php
	require_once('./Services/Component/classes/class.ilPluginAdmin.php');
	require_once('./Services/Component/classes/class.ilPlugin.php');
	require_once('./Services/UICore/classes/class.ilCtrl.php');

	// Mantis #17842
	/** @var $ilCtrl ilCtrl */
	global $ilCtrl, $ilPluginAdmin;
	if (is_null($ilPluginAdmin)) {
		$GLOBALS['ilPluginAdmin'] = new ilPluginAdmin();
	}
	if (is_null($ilCtrl)) {
		$GLOBALS['ilCtrl'] = new ilCtrl();
	}
	global $ilCtrl;

	function writeCtrlClassEntry(ilPluginSlot $slot, array $plugin_data) {
		global $ilCtrl;
		$prefix = $slot->getPrefix() . '_' . $plugin_data['id'];
		$ilCtrl->insertCtrlCalls("ilobjcomponentsettingsgui", ilPlugin::getConfigureClassName($plugin_data['name']), $prefix);
	}

	include_once("./Services/Component/classes/class.ilModule.php");
	$modules = ilModule::getAvailableCoreModules();
	foreach ($modules as $m) {
		$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE, $m["subdir"]);
		foreach ($plugin_slots as $ps) {
			include_once("./Services/Component/classes/class.ilPluginSlot.php");
			$slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
			foreach ($slot->getPluginsInformation() as $p) {
				if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p["name"]) && $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p["name"]))) {
					writeCtrlClassEntry($slot, $p);
				}
			}
		}
	}
	include_once("./Services/Component/classes/class.ilService.php");
	$services = ilService::getAvailableCoreServices();
	foreach ($services as $s) {
		$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_SERVICE, $s["subdir"]);
		foreach ($plugin_slots as $ps) {
			$slot = new ilPluginSlot(IL_COMP_SERVICE, $s["subdir"], $ps["id"]);
			foreach ($slot->getPluginsInformation() as $p) {
				if (ilPlugin::hasConfigureClass($slot->getPluginsDirectory(), $p["name"]) && $ilCtrl->checkTargetClass(ilPlugin::getConfigureClassName($p["name"]))) {
					writeCtrlClassEntry($slot, $p);
				}
			}
		}
	}
?>
<#5>
<?php
if(!$ilDB->indexExistsByFields('il_qpl_qst_fq_unit',array('question_fi')))
{
	$ilDB->addIndex('il_qpl_qst_fq_unit',array('question_fi'), 'i2');
}
?>
<#6>
<?php
$setting = new ilSetting();
$setting->set('mail_send_html', 1);
?>