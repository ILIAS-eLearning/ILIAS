<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/EventHandling/interfaces/interface.ilAppEventListener.php';

/**
 * Update skill from Services/Tracking events
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesTracking
 */
class ilSkillAppEventListener implements ilAppEventListener
{
    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent($a_component, $a_event, $a_params)
    {
        switch ($a_component) {
            case 'Services/Tracking':
                switch ($a_event) {
                    case 'updateStatus':
                        if ($a_params["status"] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                            $obj_id = $a_params["obj_id"];
                            $usr_id = $a_params["usr_id"];
                            include_once("./Services/Skill/classes/class.ilSkillResources.php");
                            include_once("./Services/Skill/classes/class.ilBasicSkill.php");
                            foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                                foreach (ilSkillResources::getTriggerLevelsForRefId($ref_id) as $sk) {
                                    ilBasicSkill::writeUserSkillLevelStatus(
                                        $sk["level_id"],
                                        $usr_id,
                                        $ref_id,
                                        $sk["tref_id"]
                                    );
                                }
                            }
                        }
                        break;
                }
                break;

            case "Services/Object":
                switch ($a_event) {
                    case "beforeDeletion":
                        $handler = new ilSkillObjDeletionHandler($a_params["object"]->getId(), $a_params["object"]->getType());
                        $handler->processDeletion();
                        break;
                }
                break;

        }
        
        return true;
    }
}
