<?php

require_once "Services/Repository/classes/class.ilObjectPlugin.php";
require_once "Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php";

/**
 * Class ilOrgUnitExtension
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtension extends ilObjectPlugin {

	/**
	 * @var ilObjOrgUnitTree
	 */
	protected $ilObjOrgUnitTree;

	/**
	 * @var int
	 */
	protected $parent_ref_id;


	/**
	 * ilOrgUnitExtension constructor.
	 *
	 * @param int $a_ref_id
	 */
	public function __construct($a_ref_id = 0) {
		global $tree;

		parent::__construct($a_ref_id);
		$this->ilObjOrgUnitTree = ilObjOrgUnitTree::_getInstance();
		$this->parent_ref_id = $tree->getParentId($a_ref_id ? $a_ref_id : $_GET['ref_id']);
	}

	/**
	 * Returns all Orgu Plugin Ids of active plugins where the Plugin wants to be shown in the tree. ($plugin->showInTree() == true)
	 *
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


	/**
	 * @return ilOrgUnitExtensionPlugin
	 * @throws ilPluginException
	 */
	protected function getPlugin() {
		if (!$this->plugin) {
			$this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext", ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
			if (!$this->plugin instanceof ilOrgUnitExtensionPlugin) {
				throw new ilPluginException("ilOrgUnitExtension: Could not instantiate plugin object for type " . $this->getType() . ".");
			}
		}

		return $this->plugin;
	}


	/**
	 * Get all user ids of employees of the underlying OrgUnit.
	 *
	 * @param bool $recursively include all employees in the suborgunits
	 * @return int[]
	 */
	public function getEmployees($recursively = false) {
		return $this->ilObjOrgUnitTree->getEmployees($this->parent_ref_id, $recursively);
	}


	/**
	 * Get all user ids of superiors of the underlying OrgUnit
	 *
	 * @param bool $recursively
	 * @return int[]
	 */
	public function getSuperiors($recursively = false) {
		return $this->ilObjOrgUnitTree->getSuperiors($this->parent_ref_id, $recursively);
	}


	/**
	 * @return ilObjOrgUnit
	 */
	public function getOrgUnit() {
		return ilObjectFactory::getInstanceByRefId($this->parent_ref_id);
	}
}