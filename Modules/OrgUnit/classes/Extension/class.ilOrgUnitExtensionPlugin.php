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
        return ilPlugin::_getImagePath(IL_COMP_MODULE, "OrgUnit", "orguext", ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $a_type), "icon_"
            . $a_type
            . ".svg");
    }


    /**
     * @param $a_id
     *
     * @return string
     */
    public static function _getName($a_id) : string
    {
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
    public function showInTree()
    {
        return false;
    }
}
