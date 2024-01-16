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

declare(strict_types=1);

class ilTestParticipantAccessFilterFactory
{
    public function __construct(
        private ilAccessHandler $access
    ) {
    }

    public function getManageParticipantsUserFilter(int $ref_id): Closure
    {
        return function (array $user_ids) use ($ref_id): array {
            return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'write',
                ilOrgUnitOperation::OP_MANAGE_PARTICIPANTS,
                $ref_id,
                $user_ids
            );
        };
    }

    public function getScoreParticipantsUserFilter(int $ref_id): Closure
    {
        return function (array $user_ids) use ($ref_id): array {
            return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'write',
                ilOrgUnitOperation::OP_SCORE_PARTICIPANTS,
                $ref_id,
                $user_ids
            );
        };
    }

    public function getAccessResultsUserFilter(int $ref_id): Closure
    {
        return function (array $user_ids) use ($ref_id): array {
            $perm = 'write';
            if ($this->access->checkAccess('tst_results', '', $ref_id, 'tst')) {
                $perm = 'tst_results';
            }

            return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                $perm,
                ilOrgUnitOperation::OP_ACCESS_RESULTS,
                $ref_id,
                $user_ids
            );
        };
    }

    public function getAccessStatisticsUserFilter(int $ref_id): Closure
    {
        return function (array $user_ids) use ($ref_id): array {
            if ($this->access->checkAccess('tst_statistics', '', $ref_id)) {
                return $user_ids;
            }

            return $this->getAccessResultsUserFilter($ref_id)($user_ids);
        };
    }
}
