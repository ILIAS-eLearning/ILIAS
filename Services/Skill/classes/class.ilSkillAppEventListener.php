<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Update skill from Services/Tracking events
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
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
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        switch ($a_component) {
            case 'Services/Tracking':
                switch ($a_event) {
                    case 'updateStatus':
                        if ($a_parameter["status"] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                            $obj_id = $a_parameter["obj_id"];
                            $usr_id = $a_parameter["usr_id"];
                            foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                                foreach (ilSkillResources::getTriggerLevelsForRefId($ref_id) as $sk) {
                                    ilBasicSkill::writeUserSkillLevelStatus(
                                        $sk["level_id"],
                                        $usr_id,
                                        $ref_id,
                                        $sk["tref_id"]
                                    );

                                    if ($sk["tref_id"] > 0) {
                                        ilPersonalSkill::addPersonalSkill($usr_id, $sk["tref_id"]);
                                    } else {
                                        ilPersonalSkill::addPersonalSkill($usr_id, $sk["base_skill_id"]);
                                    }
                                }
                            }
                        }
                        break;
                }
                break;

            case "Services/Object":
                switch ($a_event) {
                    case "beforeDeletion":
                        $handler = new ilSkillObjDeletionHandler($a_parameter["object"]->getId(), $a_parameter["object"]->getType());
                        $handler->processDeletion();
                        break;
                }
                break;

        }
        
        return true;
    }
}
