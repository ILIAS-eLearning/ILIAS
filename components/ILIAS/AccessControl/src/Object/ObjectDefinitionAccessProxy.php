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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\AccessControl\Object;

/**
 * @internal
 */
class ObjectDefinitionAccessProxy
{
    public function getClassName(string $obj_name): string
    {
        global $DIC;

        return $DIC['objDefinition']->getClassName($obj_name);
    }

    public function supportsOfflineHandling(string $obj_type): bool
    {
        global $DIC;

        return $DIC['objDefinition']->supportsOfflineHandling($obj_type);
    }

    public function isPluginTypeName(string $str): bool
    {
        global $DIC;
        return $DIC['objDefinition']->isPluginTypeName($str);
    }

    public function isPlugin(string $obj_name): bool
    {
        global $DIC;
        return $DIC['objDefinition']->isPlugin($obj_name);
    }

    public function getLocation(string $obj_name): string
    {
        global $DIC;
        return $DIC['objDefinition']->getLocation($obj_name);
    }

}
