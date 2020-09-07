<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/EventHandling/interfaces/interface.ilAppEventListener.php';
include_once './Services/Badge/classes/class.ilBadgeHandler.php';

/**
 * Trigger activity badges from events
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgeAppEventListener implements ilAppEventListener
{
    public static function handleEvent($a_component, $a_event, $a_params)
    {
        switch ($a_component) {
            case 'Services/User':
                switch ($a_event) {
                    case 'afterUpdate':
                        $user_obj = $a_params['user_obj'];
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
                        if ($a_params['status'] == ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                            ilBadgeHandler::getInstance()->triggerEvaluation(
                                'crs/course_lp',
                                $a_params['usr_id'],
                                array('obj_id' => $a_params['obj_id'])
                            );
                        }
                        break;
                }
                break;
                        
        }
    }
}
