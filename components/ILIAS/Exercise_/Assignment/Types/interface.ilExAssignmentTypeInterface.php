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
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilExAssignmentTypeInterface
{
    // Is assignment type active?
    public function isActive(): bool;

    public function usesTeams(): bool;

    public function usesFileUpload(): bool;

    // Get title of type
    public function getTitle(): string;

    public function getSubmissionType(): string;

    public function isSubmissionAssignedToTeam(): bool;

    // Clone type specific properties of an assignment
    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target): void;

    // Returns if the submission has support to web access directory.
    public function supportsWebDirAccess(): bool;

    // Returns the short string identifier
    public function getStringIdentifier(): string;
}
