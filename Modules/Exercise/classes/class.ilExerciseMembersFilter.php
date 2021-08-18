<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilExerciseMembersFilter
 *
 * @author Jesús López <lopez@leifos.de>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseMembersFilter
{
    protected array $members;
    protected ilAccessHandler $access;
    protected int $exercise_ref_id;
    protected int $user_id;

    /**
     * @param int   $a_exc_ref_id
     * @param array $a_participants_ids
     * @param int   $a_user_id      // id of the executioner, can come from CRON JOBS
     */
    public function __construct(
        int $a_exc_ref_id,
        array $a_participants_ids,
        int $a_user_id
    ) {
        global $DIC;

        $this->access = $DIC->access();
        if ($a_user_id) {
            $this->user_id = $a_user_id;
        } else {
            $this->user_id = $DIC->user()->getId();
        }

        $this->exercise_ref_id = $a_exc_ref_id;
        $this->members = $a_participants_ids;
    }

    /**
     * Filter manageable members by position or rbac access
     * @return int[]
     */
    public function filterParticipantsByAccess() : array
    {
        if ($this->access->checkAccessOfUser(
            $this->user_id,
            'edit_submissions_grades',
            '',
            $this->exercise_ref_id
        )) {
            // if access by rbac granted => return all
            return $this->members;
        }
        return $this->access->filterUserIdsByPositionOfUser(
            $this->user_id,
            'edit_submissions_grades',
            $this->exercise_ref_id,
            $this->members
        );
    }
}
