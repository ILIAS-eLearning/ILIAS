<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages random mandatory assignments of an exercise
 *
 * (business logic)
 *
 * @author killing@leifos.de
 */
class ilExcRandomAssignmentManager
{
    const DENIED_SUBMISSIONS = "has_submissions";
    const DENIED_PEER_REVIEWS = "has_peer_reviews";
    const DENIED_TEAM_ASSIGNMENTS = "has_team_assignments";

    /**
     * @var ilObjExercise
     */
    protected $exc;

    /**
     * @var int
     */
    protected $exc_id;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilExcRandomAssignmentDBRepository
     */
    protected $rand_ass_repo;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilExcSubmissionRepository
     */
    protected $submission_repo;

    /**
     * Constructor
     */
    public function __construct(
        ilObjExercise $exc,
        ilExcRandomAssignmentDBRepository $rand_ass_repo,
        ilObjUser $user = null,
        ilLanguage $lng = null
    ) {
        global $DIC;

        $this->exc = $exc;
        $this->exc_id = $this->exc->getId();
        $this->user = (is_null($user))
            ? $DIC->user()
            : $user;
        $this->rand_ass_repo = $rand_ass_repo;
        $this->lng = (is_null($lng))
            ? $DIC->language()
            : $lng;

        $this->submission_repo = new ilExcSubmissionRepository($DIC->database());
    }

    /**
     * Checks if the random assignment can be activated (if no learner has already submitted stuff)
     *
     * @return bool
     */
    public function canBeActivated()
    {
        /** @var ilExAssignment $ass */
        foreach (ilExAssignment::getInstancesByExercise($this->exc_id) as $ass) {
            if ($ass->getPeerReview() || $ass->getAssignmentType()->usesTeams()) {
                return false;
            }
        }
        return !$this->hasAnySubmission();
    }

    /**
     * Get reasons for denied activation
     *
     * @return string[]
     */
    public function getDeniedActivationReasons()
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("exc");
        $has_peer_reviews = false;
        $has_teams = false;
        /** @var ilExAssignment $ass */
        foreach (ilExAssignment::getInstancesByExercise($this->exc_id) as $ass) {
            if ($ass->getPeerReview()) {
                $has_peer_reviews = true;
            }
            if ($ass->getAssignmentType()->usesTeams()) {
                $has_teams = true;
            }
        }
        $reasons = [];
        if ($this->hasAnySubmission()) {
            $reasons[self::DENIED_SUBMISSIONS] = $lng->txt("exc_denied_has_submissions");
        }
        if ($has_peer_reviews) {
            $reasons[self::DENIED_PEER_REVIEWS] = $lng->txt("exc_denied_has_peer_reviews");
        }
        if ($has_teams) {
            $reasons[self::DENIED_TEAM_ASSIGNMENTS] = $lng->txt("exc_denied_has_team_assignments");
        }
        return $reasons;
    }

    /**
     * Checks if the random assignment can be activated (if no learner has already submitted stuff)
     *
     * @return bool
     */
    public function canBeDeactivated()
    {
        return !$this->hasAnySubmission();
    }

    /**
     * Get reasons for denied deactivation
     *
     * @return string[]
     */
    public function getDeniedDeactivationReasons()
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("exc");
        $reasons = [];
        if ($this->hasAnySubmission()) {
            $reasons[self::DENIED_SUBMISSIONS] = $lng->txt("exc_denied_has_submissions");
        }
        return $reasons;
    }

    /**
     * Is random assignment activated?
     *
     * @return bool
     */
    public function isActivated()
    {
        return ($this->exc->getPassMode() == ilObjExercise::PASS_MODE_RANDOM);
    }

    /**
     * Get total number of assignments
     *
     * @return int
     */
    public function getTotalNumberOfAssignments()
    {
        return count(ilExAssignment::getInstancesByExercise($this->exc_id));
    }

    /**
     * Get total number of mandatory assignments
     *
     * @return int
     */
    public function getNumberOfMandatoryAssignments()
    {
        return $this->exc->getNrMandatoryRandom();
    }

    /**
     * @return bool
     */
    protected function hasAnySubmission()
    {

        /** @var ilExAssignment $ass */
        foreach (ilExAssignment::getInstancesByExercise($this->exc_id) as $ass) {
            if ($this->submission_repo->hasSubmissions($ass->getId())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Needs current user to start the exercise (by selecting the random assignments)?
     * @return bool
     */
    public function needsStart()
    {
        if ($this->isActivated()) {
            $ass_of_user = $this->rand_ass_repo->getAssignmentsOfUser($this->user->getId(), $this->exc_id);
            if (count($ass_of_user) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get mandatory assignments of user
     *
     * @param int $user_id
     * @return int[] assignment ids
     */
    public function getMandatoryAssignmentsOfUser($user_id)
    {
        return $this->rand_ass_repo->getAssignmentsOfUser($user_id, $this->exc_id);
    }


    /**
     * Start exercise
     */
    public function startExercise()
    {
        if ($this->needsStart()) {
            $this->rand_ass_repo->saveAssignmentsOfUser(
                $this->user->getId(),
                $this->exc_id,
                $this->getAssignmentSelection()
            );
        }
    }
    
    /**
     * Get random assignment selection
     */
    protected function getAssignmentSelection()
    {
        $ass_ids = array_map(function ($i) {
            return $i->getId();
        }, ilExAssignment::getInstancesByExercise($this->exc_id));

        $selected = [];
        for ($i = 0; $i < $this->getNumberOfMandatoryAssignments(); $i++) {
            $j = rand(0, count($ass_ids) - 1);
            $selected[] = current(array_splice($ass_ids, $j, 1));
        }

        return $selected;
    }

    /**
     * Is assignment visible for user
     *
     * @param int $ass_id
     * @param int $user_id
     * @return bool
     */
    public function isAssignmentVisible(int $ass_id, int $user_id)
    {
        if ($this->isActivated() && !in_array($ass_id, $this->getMandatoryAssignmentsOfUser($user_id))) {
            return false;
        }
        return true;
    }
}
