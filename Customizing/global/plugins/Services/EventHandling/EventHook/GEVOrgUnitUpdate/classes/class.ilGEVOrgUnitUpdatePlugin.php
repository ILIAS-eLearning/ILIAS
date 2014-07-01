<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");


class ilGEVOrgUnitUpdatePlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVOrgUnitUpdate";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		if ($a_component !== "Services/Object" || $a_event !== "update") {
			return;
		}
		
		if ($a_parameter["obj_type"] !== "orgu") {
			return;
		}
		
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

		$gev_settings = gevSettings::getInstance();
		$utils = gevOrgUnitUtils::getInstance($a_parameter["obj_id"]);
		
		if ($utils->getType() == gevSettings::ORG_TYPE_DEFAULT) {
			if (!$utils->hasRolesForDefaultOrgUnits()) {
				$utils->addRolesForDefaultOrgUnits();
			}
		}
		else {
			if ($utils->hasRolesForDefaultOrgUnits()) {
				$utils->removeRolesForDefaultOrgUnits();
			}
		}
	}
	
}

?>