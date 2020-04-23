<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages random mandatory assignments of an exercise
 *
 * (business logic)
 *
 * @author killing@leifos.de
 */
class ilExcMandatoryAssignmentManager
{
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
     * @var ilExcRandomAssignmentManager
     */
    protected $rand_ass_manager;

    /**
     * Constructor
     */
    public function __construct(ilObjExercise $exc, ilExcRandomAssignmentManager $rand_ass_manager)
    {
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
    public function getMandatoryAssignmentsOfUser(int $user_id)
    {
        if ($this->rand_ass_manager->isActivated()) {
            return $this->rand_ass_manager->getMandatoryAssignmentsOfUser($user_id);
        }
        $r = array_map(function ($i) {
            /** @var ilExAssignment $i */
            return $i->getId();
        }, $this->set_to_mandatory_assignments);
        return $r;
    }

    /**
     * Is assignment mandatory for a user?
     *
     * @param int $ass_id
     * @param int $user_id
     * @return bool
     */
    public function isMandatoryForUser(int $ass_id, int $user_id)
    {
        return (in_array($ass_id, $this->getMandatoryAssignmentsOfUser($user_id)));
    }
}
