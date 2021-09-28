<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Helper methods for repository object plugins
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRepositoryObjectPluginSlot
{
    // Adds objects that can be created to the add new object list array
    public static function addCreatableSubObjects(array $a_obj_array) : array
    {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Repository", "robj");
        foreach ($pl_names as $pl) {
            $pl_id = $ilPluginAdmin->getId(IL_COMP_SERVICE, "Repository", "robj", $pl);
            if ($pl_id != "") {
                $a_obj_array[$pl_id] = array("name" => $pl_id, "lng" => $pl_id, "plugin" => true);
            }
        }

        return $a_obj_array;
    }
    
    // Checks whether a repository type is a plugin or not
    public static function isTypePlugin(
        string $a_type,
        bool $a_active_status = true
    ) : bool {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        
        $pname = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $a_type);
        if ($pname == "") {
            return false;
        }

        if ($ilPluginAdmin->exists(IL_COMP_SERVICE, "Repository", "robj", $pname)) {
            if (!$a_active_status ||
                $ilPluginAdmin->isActive(IL_COMP_SERVICE, "Repository", "robj", $pname)) {
                return true;
            }
        }
        return false;
    }
    
    // Check whether a repository type is a plugin which has active learning progress
    public static function isTypePluginWithLP(
        string $a_type,
        bool $a_active_status = true
    ) : bool {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        
        $pname = ilPlugin::lookupNameForId(IL_COMP_SERVICE, "Repository", "robj", $a_type);
        if ($pname == "") {
            return false;
        }

        if ($ilPluginAdmin->exists(IL_COMP_SERVICE, "Repository", "robj", $pname)) {
            if (!$a_active_status ||
                $ilPluginAdmin->isActive(IL_COMP_SERVICE, "Repository", "robj", $pname)) {
                if ($ilPluginAdmin->hasLearningProgress(IL_COMP_SERVICE, "Repository", "robj", $pname)) {
                    return true;
                }
            }
        }
        return false;
    }
}
