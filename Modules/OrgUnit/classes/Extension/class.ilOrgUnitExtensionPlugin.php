<?php

/**
 * Class ilOrgUnitExtensionPlugin
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionPlugin extends ilRepositoryObjectPlugin
{
    public function getParentTypes() : array
    {
        return ['orgu'];
    }

    public static function _getIcon(string $a_type) : string
    {
        global $DIC;
        $componentRepositoryObject = $DIC["component.repository"];

        return ilRepositoryObjectPlugin::_getImagePath(
            ilComponentInfo::TYPE_MODULES,
            "OrgUnit",
            "orguext",
            $componentRepositoryObject->getPluginById($a_type)->getName(),
            "icon_" . $a_type . ".svg"
        );
    }

    public static function _getName(string $a_id) : string
    {
        global $DIC;
        $component_repository = $DIC["component.repository"];
        return $component_repository->getPluginById($a_id)->getName();
    }

    public function showInTree(): bool
    {
        return false;
    }
}
