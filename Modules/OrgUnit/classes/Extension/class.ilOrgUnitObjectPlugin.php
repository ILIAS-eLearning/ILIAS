<?php

require_once "Services/Repository/classes/class.ilObjectPlugin.php";

/**
 * Class ilOrgUnitObjectPlugin
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitObjectPlugin extends ilObjectPlugin {

	/**
	 * Get plugin object
	 *
	 * @return ilOrgUnitObjectPlugin
	 * @throws ilPluginException
	 */
	protected function getPlugin() {
		if (!$this->plugin) {
			$this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext", ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
			if (!$this->plugin instanceof ilOrgUnitExtensionPlugin) {
				throw new ilPluginException("ilObjectPlugin: Could not instantiate plugin object for type " . $this->getType() . ".");
			}
		}

		return $this->plugin;
	}


	/**
	 * @return string[]
	 */
	public static function getActivePluginIdsForTree() {
		/**
		 * @var $plugin ilOrgUnitExtensionPlugin
		 */
		$list = array();

		$plugin_ids = ilPlugin::getActivePluginIdsForSlot(IL_COMP_MODULE, "OrgUnit", "orguext");
		foreach ($plugin_ids as $plugin_id) {
			$plugin = ilPlugin::getRepoPluginObjectByType($plugin_id);
			if ($plugin->showInTree()) {
				$list[] = $plugin_id;
			}
		}

		return $list;
	}
}