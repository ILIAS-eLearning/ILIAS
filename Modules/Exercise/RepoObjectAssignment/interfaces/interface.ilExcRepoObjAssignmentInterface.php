<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
    public function getAssignmentInfoOfObj(int $a_ref_id, int $a_user_id) : array;

    /**
     * Get assignment access info for a repository object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     */
    public function getAccessInfo(
        int $a_ref_id,
        int $a_user_id
    ) : ilExcRepoObjAssignmentAccessInfoInterface;
}
