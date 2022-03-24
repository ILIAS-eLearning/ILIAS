<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Handles exercise repository object assignments. Main entry point for consumers.
 * @author Alexander Killing <killing@leifos.de>
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

    public static function getInstance() : self
    {
        return new self();
    }

    /**
     * @return \ilExcRepoObjAssignmentInfo[]
     */
    public function getAssignmentInfoOfObj(int $a_ref_id, int $a_user_id) : array
    {
        return ilExcRepoObjAssignmentInfo::getInfo($a_ref_id, $a_user_id);
    }

    public function getAccessInfo(
        int $a_ref_id,
        int $a_user_id
    ) : ilExcRepoObjAssignmentAccessInfoInterface {
        return ilExcRepoObjAssignmentAccessInfo::getInfo($a_ref_id, $a_user_id);
    }
}
