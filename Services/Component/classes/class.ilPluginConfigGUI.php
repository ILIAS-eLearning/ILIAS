<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Parent class for all plugin config gui classes
 *
 * @author Alex Killing <alex.killing>
 * @version $Id$
 * @ingroup ServicesComponent
 */
abstract class ilPluginConfigGUI
{
	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	function executeCommand()
	{
		global $ilCtrl, $ilTabs, $lng, $tpl;

		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "ctype", $_GET["ctype"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "cname", $_GET["cname"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "slot_id", $_GET["slot_id"]);
		$ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "pname", $_GET["pname"]);

		$tpl->setTitle($lng->txt("cmps_plugin").": ".$_GET["pname"]);
		$tpl->setDescription("");

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("cmps_plugin_slot"),
			$ilCtrl->getLinkTargetByClass("ilobjcomponentsettingsgui", "showPluginSlot"));

		$this->performCommand($ilCtrl->getCmd("configure"));

	}

	abstract function performCommand($cmd);
}
?>
