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
 * Interface for assignment types
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilExcRepoObjAssignmentInterface
{
    /**
     * Get assignment(s) info of repository object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     * @return ilExcRepoObjAssignmentInfoInterface[]
     */
    public function getAssignmentInfoOfObj(int $a_ref_id, int $a_user_id): array;

    /**
     * Get assignment access info for a repository object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     */
    public function getAccessInfo(
        int $a_ref_id,
        int $a_user_id
    ): ilExcRepoObjAssignmentAccessInfoInterface;
}
