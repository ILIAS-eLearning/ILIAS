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

namespace ILIAS\Repository\PluginSlot;

use ILIAS\ILIASObject\Definition\ObjectTypeSource;
use ILIAS\ILIASObject\Definition\ObjectTypeDefinition;
use ILIAS\ILIASObject\Definition\ObjectTypeFlag;

/**
 * Adapter which provides the active repository object plugins (slot robj) as
 * object types, so core does not need to know about them. Only meant for the
 * transition, will be removed together with the plugin slot.
 */
class RepositoryObjectPluginTypes implements ObjectTypeSource
{
    private const int POSITION_OFFSET = 99992000;

    public function getObjectTypes(): array
    {
        global $DIC;
        if (!$DIC->offsetExists('component.repository') || !$DIC->offsetExists('component.factory')) {
            return [];
        }

        $types = [];
        $position = 0;
        foreach ($DIC['component.repository']->getPluginSlotById('robj')->getActivePlugins() as $plugin_info) {
            $plugin = \ilObjectPlugin::getPluginObjectByType($plugin_info->getId());
            if (!$plugin instanceof \ilRepositoryObjectPlugin) {
                continue;
            }
            $type = (new ObjectTypeDefinition(
                $plugin_info->getId(),
                $plugin->getPluginName(),
                '',
                $plugin_info->getPath() . '/classes'
            ))
                ->asLegacyPlugin()
                ->withFlags(ObjectTypeFlag::CHECKBOX, ObjectTypeFlag::ALLOW_LINK, ObjectTypeFlag::RBAC)
                ->withPositions(self::POSITION_OFFSET + $position++, self::POSITION_OFFSET);
            if ($plugin->allowCopy()) {
                $type = $type->withFlags(ObjectTypeFlag::ALLOW_COPY);
            }
            if ($plugin_info->supportsExport()) {
                $type = $type->withFlags(ObjectTypeFlag::EXPORT);
            }
            if ($plugin->useOrguPermissions()) {
                $type = $type->withFlags(ObjectTypeFlag::ORGUNIT_PERMISSIONS);
            }
            foreach ($plugin->getParentTypes() as $parent_type) {
                $type = $type->withParentType($parent_type);
            }
            $types[] = $type;
        }
        return $types;
    }
}
