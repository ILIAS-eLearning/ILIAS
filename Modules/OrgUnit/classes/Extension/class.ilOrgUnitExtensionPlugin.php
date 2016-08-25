<?php

require_once("./Services/Component/classes/class.ilPlugin.php");
require_once("Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * Class ilOrgUnitExtensionPlugin
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionPlugin extends ilRepositoryObjectPlugin {

	/**
	 * Get Component Type
	 *
	 * @return        string        Component Type
	 */
	public final function getComponentType() {
		return IL_COMP_MODULE;
	}


	/**
	 * Get Component Name.
	 *
	 * @return        string        Component Name
	 */
	public final function getComponentName() {
		return 'OrgUnit';
	}


	/**
	 * Get Slot Name.
	 *
	 * @return        string        Slot Name
	 */
	public final function getSlot() {
		return 'OrgUnitExtension';
	}


	/**
	 * Get Slot ID.
	 *
	 * @return        string        Slot Id
	 */
	public final function getSlotId() {
		return 'orguext';
	}


	/**
	 * Object initialization done by slot.
	 */
	protected final function slotInit() {
		// nothing to do here
	}


	/**
	 * @return array
	 */
	public function getParentTypes() {
		$par_types = array( "orgu" );

		return $par_types;
	}


	/**
	 * @param $a_type
	 * @param $a_size
	 * @return string
	 */
	public static function _getIcon($a_type, $a_size) {
		return ilPlugin::_getImagePath(IL_COMP_MODULE, "OrgUnit", "orguext", ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $a_type), "icon_"
		                                                                                                                                               . $a_type
		                                                                                                                                               . ".svg");
	}


	/**
	 * @param $a_id
	 * @return string
	 */
	static function _getName($a_id) {
		$name = ilPlugin::lookupNameForId(IL_COMP_MODULE, "Repository", "orguext", $a_id);
		if ($name != "") {
			return $name;
		}
	}


	/**
	 * return true iff this item should be displayed in the tree.
	 *
	 * @return bool
	 */
	public function showInTree() {
		return false;
	}
}