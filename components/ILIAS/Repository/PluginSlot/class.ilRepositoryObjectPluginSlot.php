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

/**
 * Helper methods for repository object plugins
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRepositoryObjectPluginSlot
{
    // Adds objects that can be created to the add new object list array
    public static function addCreatableSubObjects(array $a_obj_array): array
    {
        global $DIC;
        $plugins = $DIC['repository.objects']->getAll();

        foreach ($plugins as $plugin) {
            $pl_id = $plugin->getId();
            $a_obj_array[$pl_id] = ["name" => $pl_id, "lng" => $pl_id, "plugin" => true];
        }
        return $a_obj_array;
    }

    // Checks whether a repository type is a plugin or not
    public static function isTypePlugin(
        string $a_type,
        bool $a_active_status = true
    ): bool {
        global $DIC;
        return $DIC['repository.objects']->has($a_type);
    }

    // Check whether a repository type is a plugin which has active learning progress
    public static function isTypePluginWithLP(
        string $a_type,
        bool $a_active_status = true
    ): bool {
        global $DIC;
        return $DIC['repository.objects']
            ->get($a_type)
            ->supportsLearningProgress();
    }
}
