<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Trigger activity badges from events
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        switch ($a_component) {
            case 'Services/User':
                switch ($a_event) {
                    case 'afterUpdate':
                        $user_obj = $a_parameter['user_obj'];
                        ilBadgeHandler::getInstance()->triggerEvaluation(
                            'user/profile',
                            $user_obj->getId()
                        );
                        break;
                }
                break;
            
            case 'Services/Tracking':
                switch ($a_event) {
                    case 'updateStatus':
                        if ($a_parameter['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                            ilBadgeHandler::getInstance()->triggerEvaluation(
                                'crs/course_lp',
                                $a_parameter['usr_id'],
                                array('obj_id' => $a_parameter['obj_id'])
                            );
                        }
                        break;
                }
                break;
                        
        }
    }
}
