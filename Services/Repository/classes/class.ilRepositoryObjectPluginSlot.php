<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Helper methods for repository object plugins
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesRepository
*/
class ilRepositoryObjectPluginSlot
{
    /**
    * Adds objects that can be created to the add new object list array
    */
    public static function addCreatableSubObjects($a_obj_array)
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
    
    /**
    * Checks whether a repository type is a plugin or not
    */
    public static function isTypePlugin($a_type, $a_active_status = true)
    {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        
        include_once("./Services/Component/classes/class.ilPlugin.php");
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
    
    /**
     * Check whether a repository type is a plugin which has active learning progress
     *
     * @param string $a_type
     * @param bool $a_active_status
     * @return boolean
     */
    public static function isTypePluginWithLP($a_type, $a_active_status = true)
    {
        global $DIC;

        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        
        include_once("./Services/Component/classes/class.ilPlugin.php");
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
