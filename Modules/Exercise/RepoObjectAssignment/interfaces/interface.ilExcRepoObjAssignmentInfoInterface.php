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
