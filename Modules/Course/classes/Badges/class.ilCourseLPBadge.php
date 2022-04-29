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
 * Class ilCourseLPBadge
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 * @package ModulesCourse
 */
class ilCourseLPBadge implements ilBadgeType, ilBadgeAuto
{
    public function getId() : string
    {
        return "course_lp";
    }

    public function getCaption() : string
    {
        global $DIC;

        $lng = $DIC['lng'];
        return $lng->txt("badge_course_lp");
    }

    public function isSingleton() : bool
    {
        return false;
    }

    /**
     * @return string[]
     */
    public function getValidObjectTypes() : array
    {
        return ["crs"];
    }

    public function getConfigGUIInstance() : ?ilBadgeTypeGUI
    {
        return new ilCourseLPBadgeGUI();
    }

    public function evaluate(int $a_user_id, array $a_params, array $a_config) : bool
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
