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
 * Update skill from Services/Tracking events
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSkillAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        global $DIC;

        $profile_completion_manager = $DIC->skills()->internal()->manager()->getProfileCompletionManager();

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
                            //write profile completion entries if fulfilment status has changed
                            $profile_completion_manager->writeCompletionEntryForAllProfiles($usr_id);
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
    }
}
