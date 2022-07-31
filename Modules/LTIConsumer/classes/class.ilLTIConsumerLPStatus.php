<?php declare(strict_types=1);


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

class ilLTIConsumerLPStatus extends ilLPStatus
{
//    /**
//     * Get the LP status data directly from the database table
//     * This can be called from ilLTIConsumer::getLP* methods avoiding loops
//     *
//     * @param int $a_obj_id
//     * @param $a_status
//     * @return mixed
//     */
//    public static function getLPStatusDataFromDb($a_obj_id, $a_status)
//    {
//        return self::getLPStatusData($a_obj_id, $a_status);
//    }
//
//    /**
//     * Get the LP data directly from the database table
//     * This can be called from ilLTIConsumer::getLP* methods avoiding loops
//     *
//     * @param $a_obj_id
//     * @param $a_user_id
//     */
//    public static function getLPDataForUserFromDb($a_obj_id, $a_user_id) : int
//    {
//        return self::getLPDataForUser($a_obj_id, $a_user_id);
//    }


//    /**
//     * Track read access to the object
//     * Prevents a call of determineStatus() that would return "not attempted"
//     * @see ilLearningProgress::_tracProgress()
//     *
//     * @param $a_user_id
//     * @param $a_obj_id
//     * @param $a_ref_id
//     * @param string $a_obj_type
//     */
//    public static function trackAccess($a_user_id, $a_obj_id, $a_ref_id) : void
//    {
//        ilChangeEvent::_recordReadEvent('xxco', $a_ref_id, $a_obj_id, $a_user_id);
//
//        $status = self::getLPDataForUser($a_obj_id, $a_user_id);
//        if ($status == self::LP_STATUS_NOT_ATTEMPTED_NUM) {
//            self::writeStatus($a_obj_id, $a_user_id, self::LP_STATUS_IN_PROGRESS_NUM);
//            self::raiseEventStatic(
//                $a_obj_id,
//                $a_user_id,
//                self::LP_STATUS_IN_PROGRESS_NUM,
//                self::getPercentageForUser($a_obj_id, $a_user_id)
//            );
//        }
//    }

//    /**
//     * Track result from the LTI Consumer
//     *
//     * @param $a_user_id
//     * @param $a_obj_id
//     * @param $a_status
//     * @param $a_percentage
//     *
//     * @deprecated
//     */
//    public static function trackResult($a_user_id, $a_obj_id, $a_status = self::LP_STATUS_IN_PROGRESS_NUM, $a_percentage) : void
//    {
//        self::writeStatus($a_obj_id, $a_user_id, $a_status, $a_percentage, true);
//        self::raiseEventStatic($a_obj_id, $a_user_id, $a_status, $a_percentage);
//    }

//    /**
//     * Static version if ilLPStatus::raiseEvent
//     * This function is just a workaround for PHP7 until ilLPStatus::raiseEvent is declared as static
//     *
//     * @param $a_obj_id
//     * @param $a_usr_id
//     * @param $a_status
//     * @param $a_percentage
//     */
//    protected static function raiseEventStatic($a_obj_id, $a_usr_id, $a_status, $a_percentage) : void
//    {
//        global $DIC; /* @var \ILIAS\DI\Container $DIC */
//
//        $DIC->event()->raise("Services/Tracking", "updateStatus", array(
//            "obj_id" => $a_obj_id,
//            "usr_id" => $a_usr_id,
//            "status" => $a_status,
//            "percentage" => $a_percentage
//        ));
//    }
}
