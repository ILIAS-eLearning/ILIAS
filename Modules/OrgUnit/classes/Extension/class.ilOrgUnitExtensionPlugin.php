<?php

/**
 * Class ilOrgUnitExtensionPlugin
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionPlugin extends ilRepositoryObjectPlugin
{
   /**
     * @return array
     */
    public function getParentTypes() : array
    {
        $par_types = array("orgu");

        return $par_types;
    }


    /**
     * @param $a_type
     * @param $a_size
     *
     * @return string
     */
    public static function _getIcon(string $a_type) : string
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];
        return ilRepositoryObjectPlugin::_getImagePath(
            IL_COMP_MODULE,
            "OrgUnit",
            "orguext",
            $component_repository->getPluginById($a_type)->getName(),
            "icon_" . $a_type . ".svg"
        );
    }


    /**
     * @param $a_id
     *
     * @return string
     */
    public static function _getName($a_id) : string
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];
        return $component_repository->getPluginById($a_id)->getName();
    }


    /**
     * return true iff this item should be displayed in the tree.
     *
     * @return bool
     */
    public function showInTree()
    {
        return false;
    }
}
