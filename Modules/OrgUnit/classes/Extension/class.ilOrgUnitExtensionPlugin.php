<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilOrgUnitExtensionPlugin
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilOrgUnitExtensionPlugin extends ilRepositoryObjectPlugin
{
    public function getParentTypes(): array
    {
        return ['orgu'];
    }

    public static function _getIcon(string $a_type): string
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

    public static function _getName(string $a_id): string
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
