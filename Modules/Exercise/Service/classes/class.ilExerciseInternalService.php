<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise service
 *
 * (manages business logic layer)
 *
 * @author killing@leifos.de
 */
class ilExerciseInternalService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }


    /**
     * Get random assignment manager
     *
     * @return ilExcRandomAssignmentManager
     */
    public function getRandomAssignmentManager(ilObjExercise $exc, $user = null)
    {
        return new ilExcRandomAssignmentManager($exc, new ilExcRandomAssignmentDBRepository(), $user);
    }

    /**
     * Get random assignment manager
     *
     * @return ilExcMandatoryAssignmentManager
     */
    public function getMandatoryAssignmentManager(ilObjExercise $exercise)
    {
        return new ilExcMandatoryAssignmentManager($exercise, $this->getRandomAssignmentManager($exercise));
    }
}
