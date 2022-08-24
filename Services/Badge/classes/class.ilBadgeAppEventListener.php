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
 * Trigger activity badges from events
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
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
                        if ((int) $a_parameter['status'] === ilLPStatus::LP_STATUS_COMPLETED_NUM) {
                            ilBadgeHandler::getInstance()->triggerEvaluation(
                                'crs/course_lp',
                                (int) $a_parameter['usr_id'],
                                ['obj_id' => (int) $a_parameter['obj_id']]
                            );
                        }
                        break;
                }
                break;

        }
    }
}
