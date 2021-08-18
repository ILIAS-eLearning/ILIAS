<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExcRepoObjAssignmentInfoInterface
{
    // Get assignment id
    public function getId() : int;

    // Get assignment title
    public function getTitle() : string;

    /**
     * Get readable link urls to the assignment (key is the ref id)
     *
     * @return string[] assignment link url
     */
    public function getLinks() : array;

    /**
     * Check if this object has been submitted by the user provided or its team. If not, the
     * repository object is related to an assignment, but has been submitted by another user/team.
     */
    public function isUserSubmission() : bool;

    public function getExerciseId() : int;

    public function getExerciseTitle() : string;

    /**
     * Get readable ref IDs
     *
     * @return int[]
     */
    public function getReadableRefIds() : array;
}
