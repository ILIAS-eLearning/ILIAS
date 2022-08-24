<?php

declare(strict_types=0);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 * @package ilias-tracking
 */
class ilLPStatusStudyProgramme extends ilLPStatus
{
    public static function _getCountInProgress(int $a_obj_id): int
    {
        return count(self::_getInProgress($a_obj_id));
    }

    public static function _getInProgress(int $a_obj_id): array
    {
        $prg = new ilObjStudyProgramme($a_obj_id, false);
        return $prg->getIdsOfUsersWithNotCompletedAndRelevantProgress();
    }

    public static function _getCountCompleted(int $a_obj_id): int
    {
        return count(self::_getCompleted($a_obj_id));
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        $prg = new ilObjStudyProgramme($a_obj_id, false);
        return $prg->getIdsOfUsersWithCompletedProgress();
    }

    public static function _getFailed(int $a_obj_id): array
    {
        $prg = new ilObjStudyProgramme($a_obj_id, false);
        return $prg->getIdsOfUsersWithFailedProgress();
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ): int {
        $prg = new ilObjStudyProgramme($a_obj_id, false);
        $progresses = $prg->getProgressesOf($a_usr_id);

        $relevant = false;
        $failed = false;

        foreach ($progresses as $progress) {
            if ($progress->isSuccessful()) {
                if (!$progress->isSuccessfulExpired()) {
                    return ilLPStatus::LP_STATUS_COMPLETED_NUM;
                } else {
                    $failed = true;
                }
            }
            if ($progress->isRelevant()) {
                $relevant = true;
            }
            if ($progress->isFailed()) {
                $failed = true;
            }
        }
        if ($failed) {
            return ilLPStatus::LP_STATUS_FAILED_NUM;
        }
        if ($relevant) {
            return ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
        }
        return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }
}
