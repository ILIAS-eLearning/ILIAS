<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/RepoObjectAssignment/interfaces/interface.ilExcRepoObjAssignmentInterface.php");

/**
 * Handles exercise repository object assignments. Main entry point for consumers.
 *
 * @author @leifos.de
 * @ingroup
 */
class ilExcRepoObjAssignment implements ilExcRepoObjAssignmentInterface
{

    /**
     * Constructor
     *
     */
    protected function __construct()
    {
    }

    /**
     * Get instance
     *
     * @param
     * @return
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Get assignment(s) information of repository object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id if user id is provided, only readable links will be added
     * @return ilExcRepoObjAssignmentInfoInterface[]
     */
    public function getAssignmentInfoOfObj($a_ref_id, $a_user_id)
    {
        include_once("./Modules/Exercise/RepoObjectAssignment/classes/class.ilExcRepoObjAssignmentInfo.php");
        return ilExcRepoObjAssignmentInfo::getInfo($a_ref_id, $a_user_id);
    }

    /**
     * Get assignment access info for a repository object
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     * @return ilExcRepoObjAssignmentAccessInfoInterface
     */
    public function getAccessInfo($a_ref_id, $a_user_id)
    {
        include_once("./Modules/Exercise/RepoObjectAssignment/classes/class.ilExcRepoObjAssignmentAccessInfo.php");
        return ilExcRepoObjAssignmentAccessInfo::getInfo($a_ref_id, $a_user_id);
    }

    /**
     * Is access denied
     *
     * @param int $a_ref_id ref id
     * @param int $a_user_id user id
     * @return bool
     */
    public function isGranted($a_ref_id, $a_user_id)
    {
        include_once("./Modules/Exercise/RepoObjectAssignment/classes/class.ilExcRepoObjAssignmentAccessInfo.php");
        $info = ilExcRepoObjAssignmentAccessInfo::getInfo($a_ref_id, $a_user_id);
        return !$info->isGranted();
    }
}
