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

declare(strict_types=0);
/**
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusCollectionManual.php 40252 2013-03-01 12:21:49Z jluetzen $
 * @package ilias-tracking
 */
class ilLPStatusQuestions extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id): array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);

        // Exclude all users with status completed.
        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );

        return $users;
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $usr_ids = array();

        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);

        foreach ($users as $user_id) {
            // :TODO: this ought to be optimized
            $tracker = ilLMTracker::getInstanceByObjId($a_obj_id, $user_id);
            if ($tracker->getAllQuestionsCorrect()) {
                $usr_ids[] = $user_id;
            }
        }

        return $usr_ids;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;

            $tracker = ilLMTracker::getInstanceByObjId($a_obj_id, $a_usr_id);
            if ($tracker->getAllQuestionsCorrect()) {
                $status = self::LP_STATUS_COMPLETED_NUM;
            }
        }

        return $status;
    }
}
