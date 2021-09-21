<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilExAssignmentTypeInterface
{
    // Is assignment type active?
    public function isActive() : bool;

    public function usesTeams() : bool;

    public function usesFileUpload() : bool;

    // Get title of type
    public function getTitle() : string;

    public function getSubmissionType() : string;

    public function isSubmissionAssignedToTeam() : bool;

    // Clone type specific properties of an assignment
    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target) : void;

    // Returns if the submission has support to web access directory.
    public function supportsWebDirAccess() : bool;

    // Returns the short string identifier
    public function getStringIdentifier() : string;
}
