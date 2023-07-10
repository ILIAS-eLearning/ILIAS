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

namespace ILIAS\Exercise\Assignment\Mandatory;

use ILIAS\Exercise\Assignment;
use ILIAS\Exercise\Submission;

/**
 * Manages random mandatory assignments of an exercise
 * (business logic)
 * @author Alexander Killing <killing@leifos.de>
 */
class RandomAssignmentsManager
{
    public const DENIED_SUBMISSIONS = "has_submissions";
    public const DENIED_PEER_REVIEWS = "has_peer_reviews";
    public const DENIED_TEAM_ASSIGNMENTS = "has_team_assignments";

    protected \ilObjExercise $exc;
    protected int $exc_id;
    protected \ilObjUser $user;
    protected RandomAssignmentsDBRepository $rand_ass_repo;
    protected \ilLanguage $lng;
    protected Submission\SubmissionRepositoryInterface $submission_repo;

    public function __construct(
        \ilObjExercise $exc,
        RandomAssignmentsDBRepository $rand_ass_repo,
        Submission\SubmissionRepositoryInterface $submission_repo,
        \ilObjUser $user = null,
        \ilLanguage $lng = null
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

        $this->submission_repo = $submission_repo;
    }

    // Checks if the random assignment can be activated (if no learner has already submitted stuff)
    public function canBeActivated(): bool
    {
        /** @var \ilExAssignment $ass */
        foreach (\ilExAssignment::getInstancesByExercise($this->exc_id) as $ass) {
            if ($ass->getPeerReview() || $ass->getAssignmentType()->usesTeams()) {
                return false;
            }
        }
        return !$this->hasAnySubmission();
    }

    /**
     * Get reasons for denied activation
     * @return string[]
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function getDeniedActivationReasons(): array
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("exc");
        $has_peer_reviews = false;
        $has_teams = false;
        /** @var \ilExAssignment $ass */
        foreach (\ilExAssignment::getInstancesByExercise($this->exc_id) as $ass) {
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

    // Checks if the random assignment can be activated (if no learner has already submitted stuff)
    public function canBeDeactivated(): bool
    {
        return !$this->hasAnySubmission();
    }

    /**
     * Get reasons for denied deactivation
     * @return string[]
     */
    public function getDeniedDeactivationReasons(): array
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("exc");
        $reasons = [];
        if ($this->hasAnySubmission()) {
            $reasons[self::DENIED_SUBMISSIONS] = $lng->txt("exc_denied_has_submissions");
        }
        return $reasons;
    }

    // Is random assignment activated?
    public function isActivated(): bool
    {
        return ($this->exc->getPassMode() == \ilObjExercise::PASS_MODE_RANDOM);
    }

    public function getTotalNumberOfAssignments(): int
    {
        return count(\ilExAssignment::getInstancesByExercise($this->exc_id));
    }

    public function getNumberOfMandatoryAssignments(): int
    {
        return $this->exc->getNrMandatoryRandom();
    }

    protected function hasAnySubmission(): bool
    {
        /** @var \ilExAssignment $ass */
        foreach (\ilExAssignment::getInstancesByExercise($this->exc_id) as $ass) {
            if ($this->submission_repo->hasSubmissions($ass->getId())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Needs current user to start the exercise (by selecting the random assignments)?
     */
    public function needsStart(): bool
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
    public function getMandatoryAssignmentsOfUser(int $user_id): array
    {
        return $this->rand_ass_repo->getAssignmentsOfUser($user_id, $this->exc_id);
    }


    // Start exercise
    public function startExercise(): void
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
     * @return int[]
     * @throws \ilExcUnknownAssignmentTypeException
     */
    protected function getAssignmentSelection(): array
    {
        $ass_ids = array_map(function ($i) {
            return $i->getId();
        }, \ilExAssignment::getInstancesByExercise($this->exc_id));

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
    public function isAssignmentVisible(
        int $ass_id,
        int $user_id
    ): bool {
        if ($this->isActivated() && !in_array($ass_id, $this->getMandatoryAssignmentsOfUser($user_id))) {
            return false;
        }
        return true;
    }
}
