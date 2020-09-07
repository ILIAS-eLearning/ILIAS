<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
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
    public function getAssignmentInfoOfObj($a_ref_id, $a_user_id);

    /**
     * Get assignment access info for a repository object
     *
     * @param int $a_ref_id
     * @param int $a_user_id
     * @return ilExcRepoObjAssignmentAccessInfoInterface
     */
    public function isGranted($a_ref_id, $a_user_id);
}
