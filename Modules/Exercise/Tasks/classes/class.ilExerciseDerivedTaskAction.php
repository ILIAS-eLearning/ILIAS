<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Action class for derived tasks, mostly getting user reponsibilities
 * by respecting permissions as well.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseDerivedTaskAction
{
    protected ilExcMemberRepository $exc_mem_repo;
    protected ilExcAssMemberStateRepository $state_repo;
    protected ilExcTutorRepository $tutor_repo;

    public function __construct(
        ilExcMemberRepository $exc_mem_repo,
        ilExcAssMemberStateRepository $state_repo,
        ilExcTutorRepository $tutor_repo
    ) {
        $this->exc_mem_repo = $exc_mem_repo;
        $this->state_repo = $state_repo;
        $this->tutor_repo = $tutor_repo;
    }

    /**
     * Get all open assignments of a user
     * @throws ilExcUnknownAssignmentTypeException
     * @return \ilExAssignment[]
     */
    public function getOpenAssignmentsOfUser(int $user_id) : array
    {
        $user_exc_ids = $this->exc_mem_repo->getExerciseIdsOfUser($user_id);
        $assignments = [];
        foreach ($this->state_repo->getSubmitableAssignmentIdsOfUser($user_exc_ids, $user_id) as $ass_id) {
            $assignments[] = new ilExAssignment($ass_id);
            // to do: permission check
        }
        return $assignments;
    }

    /**
     * Get all open peer reviews of a user
     *
     * @return ilExAssignment[]
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getOpenPeerReviewsOfUser(int $user_id) : array
    {
        $user_exc_ids = $this->exc_mem_repo->getExerciseIdsOfUser($user_id);
        $assignments = [];
        foreach ($this->state_repo->getAssignmentIdsWithPeerFeedbackNeeded($user_exc_ids, $user_id) as $ass_id) {
            $assignments[] = new ilExAssignment($ass_id);
            // to do: permission check
        }
        return $assignments;
    }

    /**
     * Get all open gradings of a user
     *
     * @return ilExAssignment[]
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getOpenGradingsOfUser(int $user_id) : array
    {
        $user_exc_ids = $this->tutor_repo->getExerciseIdsBeingTutor($user_id);
        $assignments = [];
        foreach (array_keys($this->state_repo->getAssignmentIdsWithGradingNeeded($user_exc_ids)) as $ass_id) {
            $assignments[] = new ilExAssignment($ass_id);
            // to do: permission check
        }
        return $assignments;
    }
}
