<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

/**
 * Represents the progress of a user at one node of a study programme.
 *
 * A user could have multiple progress' on one node, since he could also have
 * multiple assignments to one node.
 */
class ilStudyProgrammeUserProgress
{
    const ACTION_MARK_ACCREDITED = "mark_accredited";
    const ACTION_UNMARK_ACCREDITED = "unmark_accredited";
    const ACTION_SHOW_INDIVIDUAL_PLAN = "show_individual_plan";
    const ACTION_REMOVE_USER = "remove_user";
    const ACTION_CHANGE_EXPIRE_DATE = "change_expire_date";
    const ACTION_CHANGE_DEADLINE = "change_deadline";

    /**
     * @var ilStudyProgrammeProgress
     */
    protected $progress;

    /**
     * @var ilStudyProgrammeProgressRepository
     */
    protected $progress_repository;

    /**
     * @var ilStudyProgrammeAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var ilStudyProgrammeEvents
     */
    protected $events;

    public function __construct(
        ilStudyProgrammeProgress $progress,
        ilStudyProgrammeProgressRepository $progress_repository,
        ilStudyProgrammeAssignmentRepository $assignment_repository,
        ilStudyProgrammeEvents $events
    ) {
        $this->progress = $progress;
        $this->progress_repository = $progress_repository;
        $this->assignment_repository = $assignment_repository;
        $this->events = $events;
    }

    /**
     * Get the program node this progress belongs to.
     *
     * Throws when the according program has no ref id.
     *
     * TODO: I'm quite sure, this will profit from caching.
     *
     * @throws ilException
     */
    public function getStudyProgramme() : ilObjStudyProgramme
    {
        $refs = ilObject::_getAllReferences($this->progress->getNodeId());
        if (!count($refs)) {
            throw new ilException(
                "ilStudyProgrammeUserAssignment::getStudyProgramme: "
                . "could not find ref_id for program '"
                . $this->progress->getNodeId() . "'."
            )
            ;
        }
        return ilObjStudyProgramme::getInstanceByRefId(array_shift($refs));
    }

    /**
     * Get the assignment this progress belongs to.
     */
    public function getAssignmentId() : int
    {
        return $this->progress->getAssignmentId();
    }

    /**
     * Get the id of the progress.
     */
    public function getId() : int
    {
        return $this->progress->getId();
    }

    /**
     * Get the id of the program node the progress belongs to.
     */
    public function getNodeId() : int
    {
        return $this->progress->getNodeId();
    }

    /**
     * Get the id of the user who is assigned.
     */
    public function getUserId() : int
    {
        return $this->progress->getUserId();
    }

    /**
     * Get the status of the progress.
     */
    public function getStatus() : int
    {
        return $this->progress->getStatus();
    }

    /**
     * Get the amount of points needed to complete the node. This is the amount
     * of points yielded for the completion of the node above as well.
     */
    public function getAmountOfPoints() : int
    {
        return $this->progress->getAmountOfPoints();
    }

    /**
     * Get the amount of points the user currently achieved.
     */
    public function getCurrentAmountOfPoints() : int
    {
        if (
            $this->isAccredited() ||
            ($this->isSuccessful() && $this->getStudyProgramme()->hasLPChildren())
        ) {
            return $this->getAmountOfPoints();
        }

        return $this->progress->getCurrentAmountOfPoints();
    }

    /**
     * Get the timestamp when the last change was made on this progress.
     */
    public function getLastChange() : DateTime
    {
        return $this->progress->getLastChange();
    }

    /**
     * Get the id of the user who did the last change on this progress.
     */
    public function getLastChangeBy() : ?int
    {
        return $this->progress->getLastChangeBy();
    }

    /**
     * Get the id of the user or course that lead to completion of this node.
     */
    public function getCompletionBy()
    {
        return $this->progress->getCompletionBy();
    }

    /**
     * Get the assignment date of this node.
     */
    public function getAssignmentDate() : DateTime
    {
        return $this->progress->getAssignmentDate();
    }

    /**
     * Get the completion date of this node.
     */
    public function getCompletionDate() : ?DateTime
    {
        return $this->progress->getCompletionDate();
    }

    /**
     * Get the deadline of this node.
     */
    public function getDeadline()
    {
        return $this->progress->getDeadline();
    }

    /**
     * Set the deadline of this node.
     */
    public function setDeadline(DateTime $deadline = null) : ilStudyProgrammeProgress
    {
        return $this->progress->setDeadline($deadline);
    }

    /**
     * Get validity of qualification
     *
     * @return DateTime | null
     */
    public function getValidityOfQualification() : ?DateTime
    {
        return $this->progress->getValidityOfQualification();
    }

    /**
     * Set validity of qualification
     */
    public function setValidityOfQualification(DateTime $date = null) : void
    {
        $this->progress->setValidityOfQualification($date);
    }

    public function storeProgress() : void
    {
        $this->progress_repository->update($this->progress);
    }

    /**
     * Delete the assignment from database.
     */
    public function delete() : void
    {
        $this->progress_repository->delete($this->progress);
    }


    /**
     * Mark this progress as accredited.
     *
     * Throws when status is not IN_PROGRESS. Throws when program node is outdated
     * and current status is NOT_RELEVANT.
     *
     * @throws ilException
     */
    public function markAccredited(int $user_id) : ilStudyProgrammeUserProgress
    {
        $prg = $this->getStudyProgramme();
        if ($this->getStatus() == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            if ($prg->getStatus() == ilStudyProgrammeSettings::STATUS_OUTDATED) {
                throw new ilException(
                    "ilStudyProgrammeUserProgress::markAccredited: "
                    . "Can't mark as accredited since program is outdated."
                );
            }
        }
        $progress = $this->progress
            ->setStatus(ilStudyProgrammeProgress::STATUS_ACCREDITED)
            ->setCompletionBy($user_id)
            ->setLastChangeBy($user_id)
            ->setLastChange(new DateTime())
            ->setCompletionDate(new DateTime())
        ;

        $this->progress_repository->update($progress);

        $assignment = $this->assignment_repository->read($this->getAssignmentId());
        if ((int) $prg->getId() === $assignment->getRootId()) {
            $this->maybeLimitProgressValidity($prg, $assignment);
        }

        $this->events->userSuccessful($this);
        $this->updateParentStatus();

        return $this;
    }

    /**
     * Set the node to in progress.
     *
     * Throws when status is not ACCREDITED.
     *
     * @throws ilException
     */
    public function unmarkAccredited() : ilStudyProgrammeUserProgress
    {
        if ($this->progress->getStatus() != ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            throw new ilException("Expected status ACCREDITED.");
        }

        $this->progress_repository->update(
            $this->progress
                ->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                ->setCompletionBy(null)
                ->setCompletionDate(null)
        );

        $this->refreshLPStatus();
        $this->updateParentStatus();

        return $this;
    }

    /**
     * Mark this progress as failed.
     *
     * Throws when status is not STATUS_COMPLETED, STATUS_ACCREDITED, STATUS_NOT_RELEVANT.
     *
     * @throws ilException
     */
    public function markFailed(int $a_user_id) : ilStudyProgrammeUserProgress
    {
        $status = array(
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            ilStudyProgrammeProgress::STATUS_ACCREDITED,
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
        );

        if (in_array($this->getStatus(), $status)) {
            throw new ilException("Can't mark as failed since program is passed.");
        }

        $this->progress
            ->setStatus(ilStudyProgrammeProgress::STATUS_FAILED)
            ->setLastChangeBy($a_user_id)
            ->setCompletionDate(null);

        $this->progress_repository->update($this->progress);
        $this->refreshLPStatus();

        return $this;
    }

    /**
     * Mark this progress as failed.
     *
     * Throws when status is not STATUS_COMPLETED, STATUS_ACCREDITED, STATUS_NOT_RELEVANT.
     *
     * @throws ilException
     */
    public function invalidate() : ilStudyProgrammeUserProgress
    {
        $status = array(
            ilStudyProgrammeProgress::STATUS_COMPLETED,
            ilStudyProgrammeProgress::STATUS_ACCREDITED,
            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
        );

        if (in_array($this->getStatus(), $status) && !$this->isSuccessfulExpired()) {
            throw new ilException("Can't mark as failed since program is passed.");
        }

        $this->progress_repository->update(
            $this->progress->invalidate()
        );

        $this->refreshLPStatus();

        return $this;
    }

    public function isInvalidated() : bool
    {
        return $this->progress->isInvalidated();
    }

    /**
     * Check, whether a the course is passed and expired due to limited validity
     */
    public function isSuccessfulExpired() : bool
    {
        if ($this->getValidityOfQualification() === null) {
            return false;
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        if (
            $this->getValidityOfQualification()->format('Y-m-d') < (new DateTime())->format('Y-m-d')
        ) {
            return true;
        }

        return false;
    }

    /**
     * Set the node to in progress.
     *
     * Throws when status is not FAILED.
     *
     * @throws ilException
     */
    public function markNotFailed(int $user_id) : ilStudyProgrammeUserProgress
    {
        if ($this->progress->getStatus() != ilStudyProgrammeProgress::STATUS_FAILED) {
            throw new ilException("Expected status FAILED.");
        }

        $this->progress_repository->update(
            $this->progress
                ->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                ->setCompletionBy(null)
                ->setLastChangeBy($user_id)
        );

        $this->refreshLPStatus();

        return $this;
    }

    /**
     * Set the node to be not relevant for the user.
     *
     * Throws when status is COMPLETED.
     *
     * @throws ilException
     * @param  int $user_id The user who marks the node as not relevant.
     * @return $this
     */
    public function markNotRelevant(int $user_id) : ilStudyProgrammeUserProgress
    {
        $this->progress_repository->update(
            $this->progress
                ->setStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
                ->setCompletionBy($user_id)
                ->setLastChangeBy($user_id)
        );

        $this->updateStatus();

        return $this;
    }

    /**
     * Set the node to be relevant for the user.
     *
     * Throws when status is not NOT_RELEVANT.
     *
     * @throws ilException
     */
    public function markRelevant(int $user_id) : ilStudyProgrammeUserProgress
    {
        if ($this->progress->getStatus() != ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            throw new ilException("Expected status IN_PROGRESS.");
        }

        $this->progress_repository->update(
            $this->progress
                ->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                ->setCompletionBy($user_id)
                ->setLastChangeBy($user_id)
        );

        $this->updateStatus();

        return $this;
    }

    /**
     * Set the amount of points the user is required to have to complete this node.
     *
     * Throws when status is completed.
     *
     * @throws ilException
     */
    public function setRequiredAmountOfPoints(
        int $a_points,
        int $user_id
    ) : ilStudyProgrammeUserProgress {
        $this->progress_repository->update(
            $this->progress
                ->setAmountOfPoints($a_points)
                ->setLastChangeBy($user_id)
        );

        $this->updateStatus();

        return $this;
    }

    /**
     * Get the maximum possible amount of points a user can achieve for
     * the completion of this node.
     *
     * If the program node runs in LP-mode this will be equal getAmountOfPoints.
     *
     * TODO: Maybe caching this value would be a good idea.
     *
     * @throws ilException
     */
    public function getMaximumPossibleAmountOfPoints(bool $only_relevant = false) : int
    {
        $prg = $this->getStudyProgramme();
        if ($prg->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            return $this->getAmountOfPoints();
        }

        $children = $prg->getChildren();
        $ass = $this->progress->getAssignmentId();
        $points = array_map(function ($child) use ($ass, $only_relevant) {
            $relevant = $child->getProgressForAssignment($ass)->isRelevant();
            if ($only_relevant) {
                if ($relevant) {
                    return $child->getProgressForAssignment($ass)->getAmountOfPoints();
                } else {
                    return 0;
                }
            } else {
                return $child->getProgressForAssignment($ass)->getAmountOfPoints();
            }
        }, $children);

        return array_reduce($points, function ($a, $b) {
            return $a + $b;
        }, 0);
    }

    /**
     * Check whether the user can achieve enough points on the subnodes to
     * be able to complete this node.
     *
     * @throws ilException
     */
    public function canBeCompleted() : bool
    {
        $prg = $this->getStudyProgramme();

        if ($prg->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            return true;
        }

        if ($this->getMaximumPossibleAmountOfPoints(true) < $this->getAmountOfPoints()) {
            // Fast track
            return false;
        }

        $children_progress = $this->getChildrenProgress();
        foreach ($children_progress as $progress) {
            if ($progress->isRelevant() && !$progress->canBeCompleted()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether there are individual modifications for the user on this program.
     */
    public function hasIndividualModifications() : bool
    {
        return $this->getLastChangeBy() !== null;
    }

    /**
     * Check whether the user was successful on this node. This is the case,
     * when the node was accredited or completed.
     */
    public function isSuccessful() : bool
    {
        $status = $this->getStatus();

        return
            $status == ilStudyProgrammeProgress::STATUS_ACCREDITED ||
            $status == ilStudyProgrammeProgress::STATUS_COMPLETED
        ;
    }

    /**
     * Check whether user as failed on this node
     */
    public function isFailed() : bool
    {
        $status = $this->getStatus();

        return $status == ilStudyProgrammeProgress::STATUS_FAILED;
    }

    /**
     * Recalculates the status according to deadline
     *
     * @throws ilException
     */
    public function recalculateFailedToDeadline() : void
    {
        $deadline = $this->getDeadline();
        $today = date(ilStudyProgrammeProgress::DATE_FORMAT);

        if ($deadline
            && $deadline->format(ilStudyProgrammeProgress::DATE_FORMAT) < $today
            && $this->progress->getStatus() === ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ) {
            $this->progress_repository->update(
                $this->progress
                    ->setStatus(ilStudyProgrammeProgress::STATUS_FAILED)
            );
        }
    }

    /**
     * Check whether the user was accredited on this node.
     */
    public function isAccredited() : bool
    {
        return $this->getStatus() == ilStudyProgrammeProgress::STATUS_ACCREDITED;
    }

    /**
     * Check whether this node is relevant for the user.
     */
    public function isRelevant() : bool
    {
        return $this->getStatus() != ilStudyProgrammeProgress::STATUS_NOT_RELEVANT;
    }

    /**
     * Update the progress from its program node. Will only update when the node
     * does not have individual modifications and is not completed.
     * Return false, when update could not be performed and true otherwise.
     *
     * @throws ilException
     * @return bool | void
     */
    public function updateFromProgramNode()
    {
        if ($this->hasIndividualModifications()) {
            return false;
        }

        if ($this->getStatus() == ilStudyProgrammeProgress::STATUS_COMPLETED) {
            return false;
        }

        $prg = $this->getStudyProgramme();

        $status = ilStudyProgrammeProgress::STATUS_NOT_RELEVANT;
        if ($prg->getStatus() == ilStudyProgrammeSettings::STATUS_ACTIVE) {
            $status = ilStudyProgrammeProgress::STATUS_IN_PROGRESS;
        }

        $this->progress_repository->update(
            $this->progress
                ->setAmountOfPoints($prg->getPoints())
                ->setStatus($status)
        );

        $this->updateStatus();
    }

    /**
     * Updates the status of this progress based on the status of the progress
     * on the sub nodes. Then update the status of the parent.
     *
     * @throws ilException
     * @return int | void
     */
    protected function updateStatus()
    {
        $prg = $this->getStudyProgramme();
        if (
            ($prg->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED &&
             $this->getStatus() != ilStudyProgrammeProgress::STATUS_ACCREDITED) ||
            $this->getStatus() == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
        ) {
            // Nothing to do here, as the status will be set by LP.
            // OR current status is NOT RELEVANT
            return;
        }

        $add = function ($a, $b) {
            return $a + $b;
        };
        $get_points = function ($child) {
            if (!$child->isSuccessful()) {
                return 0;
            }
            return $child->getAmountOfPoints();
        };

        $achieved_points = array_reduce(array_map($get_points, $this->getChildrenProgress()), $add);
        if (!$achieved_points) {
            $achieved_points = 0;
        }

        $successful = $achieved_points >= $this->getAmountOfPoints() && $this->hasSuccessfullChildren();
        $this->progress->setCurrentAmountOfPoints($achieved_points);

        if ($successful) {
            $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_COMPLETED);
            if (!$this->progress->getCompletionDate()) {
                $this->progress->setCompletionDate(new DateTime());
            }

            $assignment = $this->assignment_repository->read($this->getAssignmentId());
            if ((int) $prg->getId() === $assignment->getRootId()) {
                $this->maybeLimitProgressValidity($prg, $assignment);
            }
            $this->events->userSuccessful($this);
        } else {
            $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS);
            $this->progress->setCompletionDate(null);
        }

        $this->progress_repository->update(
            $this->progress
        );

        $this->refreshLPStatus();
        $this->updateParentStatus();
    }

    /**
     * @throws ilException
     */
    protected function hasSuccessfullChildren() : bool
    {
        foreach ($this->getChildrenProgress() as $child) {
            if ($child->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update the status of the parent of this node.
     */
    protected function updateParentStatus() : void
    {
        foreach ($this->getParentProgresses() as $parent) {
            $parent->updateStatus();
        }
    }

    /**
     * Set this node to be completed due to a completed learning progress. Will
     * only set the progress if this node is relevant and not successful.
     *
     * Throws when this node is not in LP-Mode. Throws when object that was
     * completed is no child of the node or user does not belong to this
     * progress.
     *
     * @throws ilException
     * @return bool | void
     */
    public function setLPCompleted(int $obj_id, int $usr_id)
    {
        if ($this->isSuccessful() || !$this->isRelevant()) {
            return true;
        }

        $prg = $this->getStudyProgramme();
        if ($prg->getLPMode() != ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            throw new ilException(
                "ilStudyProgrammeUserProgress::setLPCompleted: "
                . "The node '" . $prg->getId() . "' is not in LP_COMPLETED mode."
            );
        }

        if ($this->getUserId() != $usr_id) {
            throw new ilException(
                "ilStudyProgrammeUserProgress::setLPCompleted: "
                . "This progress does belong to user '" . $this->getUserId()
                . "' and not to user '$usr_id'"
            );
        }

        if (!in_array($obj_id, $prg->getLPChildrenIds())) {
            throw new ilException(
                "ilStudyProgrammeUserProgress::setLPCompleted: "
                . "Object '$obj_id' is no child of node '" . $prg->getId() . "'."
            );
        }

        $this->progress_repository->update(
            $this->progress
                ->setStatus(ilStudyProgrammeProgress::STATUS_COMPLETED)
                ->setCompletionBy($obj_id)
                ->setCompletionDate(new DateTime())
        );

        $this->refreshLPStatus();

        $assignment = $this->assignment_repository->read($this->getAssignmentId());
        if ((int) $prg->getId() === $assignment->getRootId()) {
            $this->maybeLimitProgressValidity($prg, $assignment);
        }

        $this->events->userSuccessful($this);
        $this->updateParentStatus();
    }

    /**
     * @throws Exception
     */
    protected function maybeLimitProgressValidity(
        ilObjStudyProgramme $prg,
        ilStudyProgrammeAssignment $assignment
    ) : void {
        $qualification_date = $prg->getValidityOfQualificationSettings()->getQualificationDate();
        $qualification_period = $prg->getValidityOfQualificationSettings()->getQualificationPeriod();
        if (!is_null($qualification_date)) {
            $date = $qualification_date;
        } elseif (
            ilStudyProgrammeSettings::NO_VALIDITY_OF_QUALIFICATION_PERIOD !== $qualification_period
        ) {
            $date = new DateTime();
            $date->add(new DateInterval('P' . $qualification_period . 'D'));
        } else {
            // nothing to do
            return;
        }

        $this->progress_repository->update($this->progress->setValidityOfQualification($date));

        $restart_period = $prg->getValidityOfQualificationSettings()->getRestartPeriod();
        if (ilStudyProgrammeSettings::NO_RESTART !== $restart_period) {
            $date->sub(new DateInterval('P' . $restart_period . 'D'));
            $this->assignment_repository->update($assignment->setRestartDate($date));
        }
    }

    /**
     * Get the progress on the parent node for the same assignment this progress
     * belongs to.
     *
     * @throws ilException
     * @return ilStudyProgrammeUserProgress[]
     */
    protected function getParentProgresses() : array
    {
        if ($this->getStudyProgramme()->getId() == $this->assignment_repository->read($this->getAssignmentId())->getRootId()) {
            return [];
        }

        $overall_parents = [];
        $prg = $this->getStudyProgramme();
        $parent = $prg->getParent();

        if ($parent) {
            $overall_parents[] = $parent;
        }

        foreach ($prg->getReferencesToSelf() as $ref) {
            $overall_parents[] = $ref->getParent();
        }

        return array_map(
            function ($parent) {
                return $parent->getProgressForAssignment($this->progress->getAssignmentId());
            },
            $overall_parents
        );
    }

    /**
     * Get the progresses on the child nodes of this node for the same assignment
     * this progress belongs to.
     *
     * @throws ilException
     * @return ilStudyProgrammeUserProgress[]
     */
    public function getChildrenProgress() : array
    {
        $prg = $this->getStudyProgramme();
        if ($prg->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            throw new ilException(
                "ilStudyProgrammeUserProgress::getChildrenProgress: "
                . "There is some problem in the implementation. This "
                . "method should only be callled for nodes in points "
                . "mode."
            );
        }

        $ass_id = $this->progress->getAssignmentId();

        return array_map(function ($child) use ($ass_id) {
            return $child->getProgressForAssignment($ass_id);
        }, $prg->getChildren(true));
    }

    /**
     * Get a list with the names of the children of this node that a were completed
     * or accredited for the given assignment.
     *
     * @throws ilException
     * @return string[]
     */
    public function getNamesOfCompletedOrAccreditedChildren() : array
    {
        $prg = $this->getStudyProgramme();
        $children = $prg->getChildren(true);
        $ass_id = $this->progress->getAssignmentId();
        $names = array();

        foreach ($children as $child) {
            $prgrs = $child->getProgressForAssignment($ass_id);
            if (!$prgrs->isSuccessful()) {
                continue;
            }
            $names[] = $child->getTitle();
        }

        return $names;
    }

    /**
     * Get a list with possible actions on a progress record.
     *
     * @return string[]
     */
    public static function getPossibleActions(
        int $node_id,
        int $root_prg_id,
        int $status
    ) : array {
        $actions = array();

        if ($node_id == $root_prg_id) {
            $actions[] = self::ACTION_SHOW_INDIVIDUAL_PLAN;
            $actions[] = self::ACTION_REMOVE_USER;
        }

        if ($status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            $actions[] = self::ACTION_UNMARK_ACCREDITED;
        } elseif ($status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            $actions[] = self::ACTION_MARK_ACCREDITED;
        }

        return $actions;
    }

    protected function refreshLPStatus() : void
    {
        // thanks to some caching within ilLPStatusWrapper
        // the status may not be read properly otherwise ...
        ilLPStatusWrapper::_resetInfoCaches($this->progress->getNodeId());
        ilLPStatusWrapper::_refreshStatus(
            $this->getStudyProgramme()->getId(),
            array($this->getUserId())
        );
    }

    /**
     * Updates current progress
     *
     * @throws ilException
     */
    public function updateProgress(int $user_id) : void
    {
        $this->progress_repository->update(
            $this->progress->setLastChangeBy($user_id)
        );
    }

    public function informUserForRiskToFail() : void
    {
        $this->events->userRiskyToFail($this);
    }

    /**
     * @throws ilException
     */
    public static function sendRiskyToFailMail(int $progress_id, int $usr_id) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $log = $DIC['ilLog'];
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");

        /** @var ilStudyProgrammeUserProgressDB $usr_progress_db */
        $usr_progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
        /** @var ilStudyProgrammeUserProgress $usr_progress */
        $usr_progress = $usr_progress_db->getInstanceById($progress_id);
        /** @var ilObjStudyProgramme $prg */
        $prg = $usr_progress->getStudyProgramme();

        if (!$prg->shouldSendRiskyToFailMail()) {
            $log->write("Send risky to fail mail is deactivated in study programme settings");
            return;
        }

        $subject = $lng->txt("risky_to_fail_mail_subject");
        $gender = ilObjUser::_lookupGender($usr_id);
        $name = ilObjUser::_lookupFullname($usr_id);
        $body = sprintf(
            $lng->txt("risky_to_fail_mail_body"),
            $lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $send = true;
        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue(
                ilObjUser::_lookupLogin($usr_id),
                '',
                '',
                $subject,
                $body,
                null
            );
        } catch (Exception $e) {
            $send = false;
        }

        if ($send) {
            $usr_progress_db->reminderSendFor($usr_progress->getId());
        }
    }

    public function hasSuccessStatus() : bool
    {
        return in_array(
            $this->getStatus(),
            [
                ilStudyProgrammeProgress::STATUS_COMPLETED,
                ilStudyProgrammeProgress::STATUS_ACCREDITED
            ]
        );
    }
}
