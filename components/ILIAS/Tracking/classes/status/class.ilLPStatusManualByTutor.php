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

use ILIAS\DI\Container;
use ILIAS\Tracking\DB\Factory as TrackingDBFactory;
use ILIAS\Tracking\DB\FactoryInterface as TrackingDBFactoryInterface;

class ilLPStatusManualByTutor extends ilLPStatus
{
    protected const string LNG_TEXT = 'trac_mode_manual_by_tutor';
    protected const string LNG_TEXT_INFO = 'trac_mode_manual_by_tutor_info';
    protected ilLanguage $lng;

    protected TrackingDBFactoryInterface $tracking_db_factory;

    public function __construct(int $a_obj_id)
    {
        global $DIC;
        parent::__construct($a_obj_id);
        $this->tracking_db_factory = new TrackingDBFactory($DIC->database());
    }

    public static function _getNotAttempted(int $a_obj_id): array
    {
        $users = [];
        $members = self::getMembers($a_obj_id);
        if ($members) {
            // diff in progress and completed (use stored result in LPStatusWrapper)
            $users = array_diff(
                $members,
                ilLPStatusWrapper::_getInProgress($a_obj_id)
            );
            $users = array_diff(
                $users,
                ilLPStatusWrapper::_getCompleted($a_obj_id)
            );
        }
        return $users;
    }

    /**
     * @return int[] int Array of user ids
     */
    public static function _getInProgress(int $a_obj_id): array
    {
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        // Exclude all users with status completed.
        $users = array_diff(
            $users,
            ilLPStatusWrapper::_getCompleted($a_obj_id)
        );
        if ($users) {
            // Exclude all non members
            $users = array_intersect(self::getMembers($a_obj_id), $users);
        }
        return $users;
    }

    /**
     * @return int[]
     */
    public static function _getCompleted(int $a_obj_id): array
    {
        global $DIC;
        $usr_ids = (new TrackingDBFactory($DIC->database()))->lpMarks()->repository()->readAllEntriesOfObject($a_obj_id)
            ->getSubCollectionOfElementsByCompletedStatus(true)
            ->getSubCollectionOfElementsWithDistinctUsers()
            ->asUserIdArray();
        if ($usr_ids) {
            // Exclude all non members
            $usr_ids = array_intersect(self::getMembers($a_obj_id), $usr_ids);
        }
        return $usr_ids;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (in_array($this->ilObjDataCache->lookupType($a_obj_id), ['crs', 'grp'])) {
            $lp_mark = $this->tracking_db_factory->lpMarks()->repository()->readEntryForUserOfObject(
                $a_obj_id,
                $a_usr_id
            );
            if (
                !is_null($lp_mark) &&
                $lp_mark->isCompleted()
            ) {
                $status = self::LP_STATUS_COMPLETED_NUM;
            } else {
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_usr_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;
                }
            }
        }
        return $status;
    }

    public function refreshStatus(int $a_obj_id, ?array $a_users = null): void
    {
        parent::refreshStatus($a_obj_id, $a_users);
        if (ilObject::_lookupType($a_obj_id) !== 'crs') {
            return;
        }
        $course_gui = new ilObjCourseGUI('', $a_obj_id, false);
        $in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);
        $completed = ilLPStatusWrapper::_getCompleted($a_obj_id);
        $failed = ilLPStatusWrapper::_getFailed($a_obj_id);
        $not_attempted = ilLPStatusWrapper::_getNotAttempted($a_obj_id);
        $all_active_users = array_unique(
            array_merge($in_progress, $completed, $failed, $not_attempted)
        );
        foreach ($all_active_users as $usr_id) {
            $course_gui->updateLPFromStatus(
                $usr_id,
                ilParticipants::_hasPassed($a_obj_id, $usr_id)
            );
        }
    }

    protected static function getMembers(int $a_obj_id): array
    {
        global $DIC;
        $ilObjDataCache = $DIC['ilObjDataCache'];
        if (in_array($ilObjDataCache->lookupType($a_obj_id), ['crs', 'grp'])) {
            return ilParticipants::getInstanceByObjId(
                $a_obj_id
            )->getMembers();
        }
        return [];
    }

    public static function _lookupCompletedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return [];
            }
        }
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_COMPLETED_NUM,
            $a_user_ids
        );
    }

    public static function _lookupFailedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        return [];
    }

    public static function _lookupInProgressForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        if (!$a_user_ids) {
            $a_user_ids = self::getMembers($a_obj_id);
            if (!$a_user_ids) {
                return [];
            }
        }
        return self::_lookupStatusForObject(
            $a_obj_id,
            self::LP_STATUS_IN_PROGRESS_NUM,
            $a_user_ids
        );
    }

    public function init(
        Container $DIC
    ): void {
        $this->lng = $DIC->language();
    }

    public function getLPStatusId(): string
    {
        return (string) ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR;
    }

    public function getLabel(): string
    {
        return $this->lng->txt(self::LNG_TEXT);
    }

    public function getInfo(): string
    {
        return $this->lng->txt(self::LNG_TEXT_INFO);
    }
}
