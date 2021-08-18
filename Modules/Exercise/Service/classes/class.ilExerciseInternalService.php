<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Exercise service
 *
 * (manages business logic layer)
 *
 * @author Alexander Killing <killing@leifos.de>
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
     * Get random assignment manager.
     * The manager is used if the "Pass Mode" is set to "Random Selection" in the exercise settings.
     */
    public function getRandomAssignmentManager(ilObjExercise $exc, $user = null) : ilExcRandomAssignmentManager
    {
        return new ilExcRandomAssignmentManager($exc, new ilExcRandomAssignmentDBRepository(), $user);
    }

    /**
     * Get mandatory assignment manager
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function getMandatoryAssignmentManager(ilObjExercise $exercise) : ilExcMandatoryAssignmentManager
    {
        return new ilExcMandatoryAssignmentManager($exercise, $this->getRandomAssignmentManager($exercise));
    }
}
