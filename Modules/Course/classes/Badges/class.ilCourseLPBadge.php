<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";
require_once "./Services/Badge/interfaces/interface.ilBadgeAuto.php";

/**
 * Class ilCourseLPBadge
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesCourse
 */
class ilCourseLPBadge implements ilBadgeType, ilBadgeAuto
{
    public function getId()
    {
        return "course_lp";
    }
    
    public function getCaption()
    {
        global $DIC;

        $lng = $DIC['lng'];
        return $lng->txt("badge_course_lp");
    }
    
    public function isSingleton()
    {
        return false;
    }
    
    public function getValidObjectTypes()
    {
        return array("crs");
    }
    
    public function getConfigGUIInstance()
    {
        include_once "Modules/Course/classes/Badges/class.ilCourseLPBadgeGUI.php";
        return new ilCourseLPBadgeGUI();
    }
    
    public function evaluate($a_user_id, array $a_params, array $a_config)
    {
        $subitem_obj_ids = array();
        foreach ($a_config["subitems"] as $ref_id) {
            $subitem_obj_ids[$ref_id] = ilObject::_lookupObjId($ref_id);
        }
        
        $trigger_subitem_id = $a_params["obj_id"];
                
        // relevant for current badge instance?
        if (in_array($trigger_subitem_id, $subitem_obj_ids)) {
            $completed = true;
            
            // check if all subitems are completed now
            foreach ($a_config["subitems"] as $subitem_id) {
                $subitem_obj_id = $subitem_obj_ids[$subitem_id];
                if (ilLPStatus::_lookupStatus($subitem_obj_id, $a_user_id) != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                    $completed = false;
                    break;
                }
            }
            
            return $completed;
        }
        
        return false;
    }
}
