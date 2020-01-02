<?php
/* Copyright (c) 2018 Extended GPL, see docs/LICENSE */

/**
 * Class ilExerciseMembersFilter
 *
 * @author Jesús López <lopez@leifos.de>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
class ilExerciseMembersFilter
{
    /**
     * @var \ilAccessHandler
     */
    protected $members;

    /**
     * @var \ILIAS\DI\RBACServices
     */
    private $access;

    /**
     * @var int
     */
    protected $exercise_ref_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * Constructor
     *
     * @param int $a_user_id User id of the executioner, can come from CRON JOBS
     * @param int $a_exc_ref_id
     * @param array $a_participants_ids
     */
    public function __construct(int $a_exc_ref_id, array $a_participants_ids, int $a_user_id)
    {
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
