<?php

require_once("./Services/Component/classes/class.ilPlugin.php");
require_once("Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

abstract class ilOrgUnitExtensionPlugin extends ilRepositoryObjectPlugin {
	/**
	 * Get Component Type
	 *
	 * @return        string        Component Type
	 */
	final function getComponentType() {
		return IL_COMP_MODULE;
	}

	/**
	 * Get Component Name.
	 *
	 * @return        string        Component Name
	 */
	final function getComponentName() {
		return 'OrgUnit';
	}

	/**
	 * Get Slot Name.
	 *
	 * @return        string        Slot Name
	 */
	final function getSlot() {
		return 'OrgUnitExtension';
	}

	/**
	 * Get Slot ID.
	 *
	 * @return        string        Slot Id
	 */
	final function getSlotId() {
		return 'orguext';
	}

	/**
	 * Object initialization done by slot.
	 */
	protected final function slotInit() {
		// nothing to do here
	}

	public function getParentTypes() {
		$par_types = array("orgu");
		return $par_types;
	}

	/**
	 * Get Icon
	 */
	public static function _getIcon($a_type, $a_size)
	{
		return ilPlugin::_getImagePath(IL_COMP_MODULE, "OrgUnit", "orguext",
			ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext" ,$a_type),
			"icon_".$a_type.".svg");
	}

	/**
	 * Get class name
	 */
	static function _getName($a_id)
	{
		$name = ilPlugin::lookupNameForId(IL_COMP_MODULE, "Repository", "orguext",$a_id);
		if ($name != "")
		{
			return $name;
		}
	}

	/**
	 * return true iff this item should be displayed in the tree.
	 * @return bool
	 */
	public function showInTree() {
		return false;
	}

}