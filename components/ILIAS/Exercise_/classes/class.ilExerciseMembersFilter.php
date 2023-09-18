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
    public function filterParticipantsByAccess(): array
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
