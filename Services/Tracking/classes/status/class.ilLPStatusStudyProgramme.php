<?php

declare(strict_types=1);

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

class ilLPStatusStudyProgramme extends ilLPStatus
{
    protected static function getAssignments(int $obj_id, int $usr_id = null): array
    {
        $dic = ilStudyProgrammeDIC::dic();
        $repo = $dic['repo.assignment'];

        if ($usr_id) {
            $usr_id = [$usr_id];
        }
        $assignments = $repo->getAllForNodeIsContained((int) $obj_id, $usr_id);

        //restarted assignments will lose validity for LPStatus
        $assignments = array_filter($assignments, fn ($ass) => !$ass->isRestarted());

        return $assignments;
    }

    //determine a status based on a single users collection of assignments.
    protected static function getStatusForAssignments(array $assignments, int $prg_obj_id): int
    {
        $now = new DateTimeImmutable();
        $pgss = [];
        foreach ($assignments as $ass) {
            $pgs = $ass->getProgressForNode($prg_obj_id);
            $pgss[$ass->getId()] = $pgs;
        }

        //use the highest assignment first
        sort($pgss);
        $pgss = array_reverse($pgss);
        $pgs = reset($pgss);

        if (!$pgs || !$pgs->isRelevant()) {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }

        if ($pgs->hasValidQualification($now)) {
            return ilLPStatus::LP_STATUS_COMPLETED_NUM;
        }

        //successful, but expired
        //or failed
        if ($pgs->isSuccessful() || $pgs->isFailed()) {
            return ilLPStatus::LP_STATUS_FAILED_NUM;
        }

        return ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
    }

    /**
     * @param ilPRGAssignment[] $assignments
     */
    protected static function getAssignmentsLPMatrix(array $assignments, int $prg_obj_id): array
    {
        $matrix = [
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => [],
            ilLPStatus::LP_STATUS_IN_PROGRESS_NUM => [],
            ilLPStatus::LP_STATUS_COMPLETED_NUM => [],
            ilLPStatus::LP_STATUS_FAILED_NUM => []
        ];

        $user_centric = [];
        foreach ($assignments as $ass) {
            $usr_id = $ass->getUserId();
            if (!array_key_exists($usr_id, $user_centric)) {
                $user_centric[$usr_id] = [];
            }
            $user_centric[$usr_id][] = $ass;
        }
        foreach ($user_centric as $usr_id => $assignments) {
            $status = self::getStatusForAssignments($assignments, $prg_obj_id);
            $matrix[$status][] = $usr_id;
        }

        return $matrix;
    }

    public static function _getCountInProgress($a_obj_id): int
    {
        throw new \Exception('called');
        return count(self::_getInProgress($a_obj_id));
    }

    public static function _getInProgress($a_obj_id): array
    {
        $assignments = self::getAssignments((int) $a_obj_id);
        $matrix = self::getAssignmentsLPMatrix($assignments, (int) $a_obj_id);
        return $matrix[ilLPStatus::LP_STATUS_IN_PROGRESS_NUM];
    }

    public static function _getCountCompleted($a_obj_id): int
    {
        return count(self::_getCompleted($a_obj_id));
    }

    public static function _getCompleted($a_obj_id): array
    {
        $assignments = self::getAssignments((int) $a_obj_id);
        $matrix = self::getAssignmentsLPMatrix($assignments, (int) $a_obj_id);
        return $matrix[ilLPStatus::LP_STATUS_COMPLETED_NUM];
    }

    public static function _getFailed($a_obj_id): array
    {
        $assignments = self::getAssignments((int) $a_obj_id);
        $matrix = self::getAssignmentsLPMatrix($assignments, (int) $a_obj_id);
        return $matrix[ilLPStatus::LP_STATUS_FAILED_NUM];
    }

    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null): int
    {
        $assignments = self::getAssignments((int) $a_obj_id, (int) $a_user_id);
        return self::getStatusForAssignments($assignments, (int) $a_obj_id);
    }
}
