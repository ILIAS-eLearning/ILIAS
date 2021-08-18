<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Manages random mandatory assignments of an exercise
 *
 * (business logic)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcMandatoryAssignmentManager
{
    protected ilObjExercise $exc;
    protected int $exc_id;
    protected ilObjUser $user;
    protected ilExcRandomAssignmentManager $rand_ass_manager;
    /**
     * @var ilExAssignment[]
     */
    protected array $assignments;
    /**
     * @var ilExAssignment[]
     */
    protected array $set_to_mandatory_assignments;

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function __construct(
        ilObjExercise $exc,
        ilExcRandomAssignmentManager $rand_ass_manager
    ) {
        $this->exc = $exc;
        $this->exc_id = $this->exc->getId();
        $this->rand_ass_manager = $rand_ass_manager;
        $this->assignments = ilExAssignment::getInstancesByExercise($exc->getId());

        $this->set_to_mandatory_assignments = array_filter($this->assignments, function ($i) {
            /** @var ilExAssignment $i */
            if ($i->getMandatory()) {
                return true;
            }
            return false;
        });
    }

    /**
     * Get mandatory assignments for user
     *
     * @param int $user_id
     * @return int[] assigment ids
     */
    public function getMandatoryAssignmentsOfUser(
        int $user_id
    ) : array {
        if ($this->rand_ass_manager->isActivated()) {
            return $this->rand_ass_manager->getMandatoryAssignmentsOfUser($user_id);
        }
        return array_map(function ($i) {
            /** @var ilExAssignment $i */
            return $i->getId();
        }, $this->set_to_mandatory_assignments);
    }

    // Is assignment mandatory for a user?
    public function isMandatoryForUser(int $ass_id, int $user_id) : bool
    {
        return (in_array($ass_id, $this->getMandatoryAssignmentsOfUser($user_id)));
    }
}
