<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExAssignmentTypeInterface
{
    /**
     * Is assignment type active?
     *
     * @return bool
     */
    public function isActive();

    /**
     * Uses teams
     *
     * @return bool
     */
    public function usesTeams();

    /**
     * Uses file upload
     *
     * @return bool
     */
    public function usesFileUpload();

    /**
     * Get title of type
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get submission type
     *
     * @return string
     */
    public function getSubmissionType();

    /**
     * Get submission type
     *
     * @return string
     */
    public function isSubmissionAssignedToTeam();

    /**
     * Clone type specific properties of an assignment
     *
     * @param ilExAssignment $source
     * @param ilExAssignment $target
     */
    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target);
}
