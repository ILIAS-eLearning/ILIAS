<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExcRepoObjAssignmentInfoInterface
{
    /**
     * Get assignment id
     *
     * @return int assignment id
     */
    public function getId();

    /**
     * Get assignment title
     *
     * @return int assignment id
     */
    public function getTitle();

    /**
     * Get readable link urls to the assignment (key is the ref id)
     *
     * @return string[] assignment link url
     */
    public function getLinks();

    /**
     * Check if this object has been submitted by the user provided or its team. If not, the
     * repository object is related to an assignment, but has been submitted by another user/team.
     *
     * @return bool
     */
    public function isUserSubmission();

    /**
     * Get exercise id
     *
     * @return int
     */
    public function getExerciseId();

    /**
     * Get exercise title
     *
     * @return string
     */
    public function getExerciseTitle();

    /**
     * Get readable ref IDs
     *
     * @return int[]
     */
    public function getReadableRefIds();
}
