<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeProgress.php");

/**
 * Represents the progress of a user at one node of a study programme.
 *
 * A user could have multiple progress' on one node, since he could also have
 * multiple assignments to one node.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilStudyProgrammeUserProgress
{
    protected $progress; // ilStudyProgrammeProgress

    /**
     * Throws when id does not refer to a study programme progress.
     *
     * Expects an array [assignment_id, program_node_id, user_id] or an
     * ilStudyProgress as first parameter.
     *
     * @throws ilException
     * @param int[] | ilStudyProgrammeAssignment $a_ids_or_model
     */
    public function __construct($a_ids_or_model)
    {
        if ($a_ids_or_model instanceof ilStudyProgrammeProgress) {
            $this->progress = $a_ids_or_model;
        } else {
            if (count($a_ids_or_model) != 3) {
                throw new ilException("ilStudyProgrammeUserProgress::__construct: "
                                     . "expected array with 3 items.");
            }

            // TODO: ActiveRecord won't be caching the model objects, since
            // we are not using find. Maybe we should do this ourselves??
            // Or should we instead cache in getInstance?
            $this->progress = array_shift(
                ilStudyProgrammeProgress::where(array( "assignment_id" => $a_ids_or_model[0]
                            , "prg_id" => $a_ids_or_model[1]
                            , "usr_id" => $a_ids_or_model[2]
                            ))->get()
            );
        }
        if ($this->progress === null) {
            throw new ilException("ilStudyProgrammeUserProgress::__construct: Could not find progress.");
        }
    }

    /**
     * Get the program node where this progress belongs to was made.
     *
     * Throws when program this assignment is about has no ref id.
     *
     * TODO: I'm quite sure, this will profit from caching.
     *
     * @throws ilException
     * @return ilObjStudyProgramme
     */
    public function getStudyProgramme()
    {
        require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
        $refs = ilObject::_getAllReferences($this->progress->getNodeId());
        if (!count($refs)) {
            throw new ilException("ilStudyProgrammeUserAssignment::getStudyProgramme: "
                                 . "could not find ref_id for program '"
                                 . $this->progress->getNodeId() . "'.");
        }
        return ilObjStudyProgramme::getInstanceByRefId(array_shift($refs));
    }

    /**
     * Get the assignment this progress belongs to.
     *
     * @return ilStudyProgrammeUserAssignment
     */
    public function getAssignment()
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
        return ilStudyProgrammeUserAssignment::getInstance($this->progress->getAssignmentId());
    }

    /**
     * Get the id of the progress.
     *
     * @return int
     */
    public function getId()
    {
        return $this->progress->getId();
    }

    /**
     * Get the id of the program node the progress belongs to.
     *
     * @return int
     */
    public function getNodeId()
    {
        return $this->progress->getNodeId();
    }

    /**
     * Get the id of the user who is assigned.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->progress->getUserId();
    }

    /**
     * Get the status of the progress.
     *
     * @return ilStudyProgrammeProgress::$STATUS
     */
    public function getStatus()
    {
        return $this->progress->getStatus();
    }

    /**
     * Get the amount of points needed to complete the node. This is the amount
     * of points yielded for the completion of the node above as well.
     *
     * @return int
     */
    public function getAmountOfPoints()
    {
        return $this->progress->getAmountOfPoints();
    }

    /**
     * Get the amount of points the user currently achieved.
     *
     * @return int
     */
    public function getCurrentAmountOfPoints()
    {
        if ($this->isAccredited()
            || ($this->isSuccessful() && $this->getStudyProgramme()->hasLPChildren())) {
            return $this->getAmountOfPoints();
        }
        return $this->progress->getCurrentAmountOfPoints();
    }

    /**
     * Get the timestamp when the last change was made on this progress.
     *
     * @return ilDateTime
     */
    public function getLastChange()
    {
        return $this->progress->getLastChange();
    }

    /**
     * Get the id of the user who did the last change on this progress.
     *
     * @return int
     */
    public function getLastChangeBy()
    {
        return $this->progress->getLastChangeBy();
    }

    /**
     * Get the id of the user or course that lead to completion of this node.
     *
     * @return int | null
     */
    public function getCompletionBy()
    {
        return $this->progress->getCompletionBy();
    }

    /**
     * Get the deadline of this node.
     *
     * @return ilDateTime | null
     */
    public function getDeadline()
    {
        return $this->progress->getDeadline();
    }

    /**
     * Set the deadline of this node.
     *
     * @param ilDateTime | null 	$deadline
     */
    public function setDeadline($deadline)
    {
        return $this->progress->setDeadline($deadline);
    }

    /**
     * Delete the assignment from database.
     */
    public function delete()
    {
        $this->progress->delete();
    }


    /**
     * Mark this progress as accredited.
     *
     * Throws when status is not IN_PROGRESS. Throws when program node is outdated
     * and current status is NOT_RELEVANT.
     *
     * @throws ilException
     * @param int $a_user_id The user who performed the operation.
     * @return $this
     */
    public function markAccredited($a_user_id)
    {
        if ($this->getStatus() == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            $prg = $this->getStudyProgramme();
            if ($prg->getStatus() == ilStudyProgramme::STATUS_OUTDATED) {
                throw new ilException("ilStudyProgrammeUserProgress::markAccredited: "
                                     . "Can't mark as accredited since program is outdated.");
            }
        }

        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_ACCREDITED)
                       ->setCompletionBy($a_user_id)
                       ->update();

        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeEvents.php");
        ilStudyProgrammeEvents::userSuccessful($this);

        $this->updateParentStatus();
        return $this;
    }

    /**
     * Set the node to in progress.
     *
     * Throws when status is not ACCREDITED.
     *
     * @throws ilException
     * @return $this
     */
    public function unmarkAccredited()
    {
        if ($this->progress->getStatus() != ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            throw new ilException("Expected status ACCREDITED.");
        }

        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                       ->setCompletionBy(null)
                       ->update();

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
     * @param int $a_user_id The user who performed the operation.
     * @return $this
     */
    public function markFailed($a_user_id)
    {
        $status = array(ilStudyProgrammeProgress::STATUS_COMPLETED
            , ilStudyProgrammeProgress::STATUS_ACCREDITED
            , ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
        );

        if (in_array($this->getStatus(), $status)) {
            throw new ilException("Can't mark as failed since program is passed.");
        }

        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_FAILED)
            ->setLastChangeBy($a_user_id)
            ->update();

        $this->refreshLPStatus();

        return $this;
    }

    /**
     * Set the node to in progress.
     *
     * Throws when status is not FAILED.
     *
     * @throws ilException
     * @return $this
     */
    public function markNotFailed()
    {
        if ($this->progress->getStatus() != ilStudyProgrammeProgress::STATUS_FAILED) {
            throw new ilException("Expected status FAILED.");
        }

        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                       ->setCompletionBy(null)
                       ->setLastChangeBy($a_user_id)
                       ->update();

        $this->refreshLPStatus();

        return $this;
    }

    /**
     * Set the node to be not relevant for the user.
     *
     * Throws when status is COMPLETED.
     *
     * @throws ilException
     * @param  int $a_user_id The user who marks the node as not relevant.
     * @return $this
     */
    public function markNotRelevant($a_user_id)
    {
        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
                       ->setCompletionBy($a_user_id)
                       ->setLastChangeBy($a_user_id)
                       ->update();

        $this->updateStatus();
        return $this;
    }

    /**
     * Set the node to be relevant for the user.
     *
     * Throws when status is not NOT_RELEVANT.
     *
     * @throws ilException
     * @param  int $a_user_id The user who marks the node as not relevant.
     * @return $this
     */
    public function markRelevant($a_user_id)
    {
        if ($this->progress->getStatus() != ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            throw new ilException("Expected status IN_PROGRESS.");
        }

        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                       ->setCompletionBy($a_user_id)
                       ->setLastChangeBy($a_user_id)
                       ->update();

        $this->updateStatus();
        return $this;
    }

    /**
     * Set the amount of points the user is required to have to complete this node.
     *
     * Throws when status is completed.
     *
     * @throws ilException
     * @param int $a_points    The amount of points the user needs for completion.
     * @param int $a_user_id   The id of the user who did the modification.
     * @return $this
     */
    public function setRequiredAmountOfPoints($a_points, $a_user_id)
    {
        $this->progress->setAmountOfPoints($a_points)
                       ->setLastChangeBy($a_user_id)
                       ->update();

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
     * @param $only_relevant 	boolean 	true if check is nesserary the progress is relevant
     *
     * @return int
     */
    public function getMaximumPossibleAmountOfPoints($only_relevant = false)
    {
        $prg = $this->getStudyProgramme();
        if ($prg->getLPMode() == ilStudyProgramme::MODE_LP_COMPLETED) {
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
     * @return bool
     */
    public function canBeCompleted()
    {
        $prg = $this->getStudyProgramme();

        if ($prg->getLPMode() == ilStudyProgramme::MODE_LP_COMPLETED) {
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
     *
     * @return bool
     */
    public function hasIndividualModifications()
    {
        return $this->getLastChangeBy() !== null;
    }

    /**
     * Check whether the user was successful on this node. This is the case,
     * when the node was accredited or completed.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $status = $this->getStatus();

        return $status == ilStudyProgrammeProgress::STATUS_ACCREDITED
            || $status == ilStudyProgrammeProgress::STATUS_COMPLETED;
    }

    /**
     * Check wether user as failed on this node
     *
     * @return bool
     */
    public function isFailed()
    {
        $status = $this->getStatus();

        return $status == ilStudyProgrammeProgress::STATUS_FAILED;
    }

    /**
     * Recalculates the status according to deadline
     *
     * @return viod
     */
    public function recalculateFailedToDeadline()
    {
        $deadline = $this->getDeadline();
        $today = date("Y-m-d");

        if ($deadline && $deadline->get(IL_CAL_DATE) < $today) {
            $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_FAILED)
                ->update();
        }
    }

    /**
     * Check whether the user was accredited on this node.
     *
     * @return bool
     */
    public function isAccredited()
    {
        $status = $this->getStatus();

        return $status == ilStudyProgrammeProgress::STATUS_ACCREDITED;
    }

    /**
     * Check whether this node is relevant for the user.
     *
     * @return bool
     */
    public function isRelevant()
    {
        return $this->getStatus() != ilStudyProgrammeProgress::STATUS_NOT_RELEVANT;
    }

    /**
     * Update the progress from its program node. Will only update when the node
     * does not have individual modifications and is not completed.
     * Return false, when update could not be performed and true otherwise.
     *
     * @return bool
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
        $this->progress->setAmountOfPoints($prg->getPoints())
                       ->setStatus(
                           $prg->getStatus() == ilStudyProgramme::STATUS_ACTIVE
                                    ? ilStudyProgrammeProgress::STATUS_IN_PROGRESS
                                    : ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
                       )
                       ->update();

        $this->updateStatus();
    }

    /**
     * Updates the status of this progress based on the status of the progress
     * on the sub nodes. Then update the status of the parent.
     */
    protected function updateStatus()
    {
        $prg = $this->getStudyProgramme();
        if (($prg->getLPMode() == ilStudyProgramme::MODE_LP_COMPLETED
            && $this->getStatus() != ilStudyProgrammeProgress::STATUS_ACCREDITED)
            || $this->getStatus() == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
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
        $status = $this->getStatus();

        $this->progress->setCurrentAmountOfPoints($achieved_points);
        if ($successful) {
            $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_COMPLETED);
            require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeEvents.php");
            ilStudyProgrammeEvents::userSuccessful($this);
        } else {
            $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS);
        }

        $this->progress->update();
        $this->refreshLPStatus();
        $this->updateParentStatus();
    }

    protected function hasSuccessfullChildren()
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
    protected function updateParentStatus()
    {
        $parent = $this->getParentProgress();
        if ($parent) {
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
     */
    public function setLPCompleted($a_obj_id, $a_usr_id)
    {
        if ($this->isSuccessful() || !$this->isRelevant()) {
            return true;
        }

        $prg = $this->getStudyProgramme();
        if ($prg->getLPMode() != ilStudyProgramme::MODE_LP_COMPLETED) {
            throw new ilException("ilStudyProgrammeUserProgress::setLPCompleted: "
                                 . "The node '" . $prg->getId() . "' is not in LP_COMPLETED mode.");
        }
        if ($this->getUserId() != $a_usr_id) {
            throw new ilException("ilStudyProgrammeUserProgress::setLPCompleted: "
                                 . "This progress does belong to user '" . $this->getUserId()
                                 . "' and not to user '$a_usr_id'");
        }
        if (!in_array($a_obj_id, $prg->getLPChildrenIds())) {
            throw new ilException("ilStudyProgrammeUserProgress::setLPCompleted: "
                                 . "Object '$a_obj_id' is no child of node '" . $prg->getId() . "'.");
        }

        $this->progress->setStatus(ilStudyProgrammeProgress::STATUS_COMPLETED)
                       ->setCompletionBy($a_obj_id)
                       ->update();

        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeEvents.php");
        ilStudyProgrammeEvents::userSuccessful($this);

        $this->refreshLPStatus();
        $this->updateParentStatus();
    }

    /**
     * Get the progress on the parent node for the same assignment this progress
     * belongs to.
     */
    protected function getParentProgress()
    {
        $prg = $this->getStudyProgramme();
        $parent = $prg->getParent();
        if (!$parent) {
            return null;
        }

        if ($this->getStudyProgramme()->getId() == $this->getAssignment()->getStudyProgramme()->getId()) {
            return null;
        }

        return $parent->getProgressForAssignment($this->progress->getAssignmentId());
    }

    /**
     * Get the progresses on the child nodes of this node for the same assignment
     * this progress belongs to.
     *
     * @return ilStudyProgrammeUserProgress[]
     */
    public function getChildrenProgress()
    {
        $prg = $this->getStudyProgramme();
        if ($prg->getLPMode() == ilStudyProgramme::MODE_LP_COMPLETED) {
            throw new ilException("ilStudyProgrammeUserProgress::getChildrenProgress: "
                                 . "There is some problem in the implementation. This "
                                 . "method should only be callled for nodes in points "
                                 . "mode.");
        }

        $ass_id = $this->progress->getAssignmentId();
        return array_map(function ($child) use ($ass_id) {
            return $child->getProgressForAssignment($ass_id);
        }, $prg->getChildren());
    }

    /**
     * Get a list with the names of the children of this node that a were completed
     * or accredited for the given assignment.
     *
     * @param int $a_assignment_id
     * @return string[]
     */
    public function getNamesOfCompletedOrAccreditedChildren()
    {
        $prg = $this->getStudyProgramme();
        $children = $prg->getChildren();
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

    const ACTION_MARK_ACCREDITED = "mark_accredited";
    const ACTION_UNMARK_ACCREDITED = "unmark_accredited";
    const ACTION_SHOW_INDIVIDUAL_PLAN = "show_individual_plan";
    const ACTION_REMOVE_USER = "remove_user";
    /**
     * Get a list with possible actions on a progress record.
     *
     * @param int $a_node_id	object_id!
     * @param int $a_belongs	object_id!
     * @param int $a_status
     */
    public static function getPossibleActions($a_node_id, $a_root_prg_id, $a_status)
    {
        $actions = array();
        if ($a_node_id == $a_root_prg_id) {
            $actions[] = self::ACTION_SHOW_INDIVIDUAL_PLAN;
            $actions[] = self::ACTION_REMOVE_USER;
        }
        if ($a_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            $actions[] = self::ACTION_UNMARK_ACCREDITED;
        } elseif ($a_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            $actions[] = self::ACTION_MARK_ACCREDITED;
        }
        return $actions;
    }

    protected function refreshLPStatus()
    {
        require_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->getStudyProgramme()->getId(), array($this->getUserId()));
    }

    /**
     * Updates current progress
     *
     * @param int 	$user_id
     *
     * @return void
     */
    public function updateProgress($user_id)
    {
        $this->progress->setLastChangeBy($user_id)
            ->update();
    }
}
