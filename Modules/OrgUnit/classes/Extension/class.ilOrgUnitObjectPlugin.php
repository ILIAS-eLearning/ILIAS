<?php

require_once "Services/Repository/classes/class.ilObjectPlugin.php";

abstract class ilOrgUnitObjectPluginGUI extends ilObjectPlugin {
	/**
	 * Get plugin object
	 * @return object plugin object
	 * @throws ilPluginException
	 */
	protected function getPlugin() {
		if (!$this->plugin) {
			$this->plugin =
				ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext",
					ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
			if (!is_object($this->plugin)) {
				throw new ilPluginException("ilObjectPlugin: Could not instantiate plugin object for type " . $this->getType() . ".");
			}
		}
		return $this->plugin;
	}

	/**
	 * @return string[]
	 */
	public static function getActivePluginIdsForTree() {
		$list = array();
		$pluginIds = ilPlugin::getActivePluginIdsForSlot(IL_COMP_MODULE, "OrgUnit", "orguext");
		foreach($pluginIds as $pluginId) {
			$plugin = ilPlugin::getRepoPluginObjectByType($pluginId);
			if($plugin->showInTree())
				$list[] = $pluginId;
		}
		return $list;
	}
}