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
 * Text type
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssTypeText implements ilExAssignmentTypeInterface
{
    protected ilLanguage$lng;

    /**
     * Constructor
     *
     * @param ilLanguage|null $a_lng
     */
    public function __construct(ilLanguage $a_lng = null)
    {
        global $DIC;

        $this->lng = ($a_lng)
            ?: $DIC->language();
    }

    public function isActive(): bool
    {
        return true;
    }

    public function usesTeams(): bool
    {
        return false;
    }

    public function usesFileUpload(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        return $lng->txt("exc_type_text");
    }

    public function getSubmissionType(): string
    {
        return ilExSubmission::TYPE_TEXT;
    }

    public function isSubmissionAssignedToTeam(): bool
    {
        return false;
    }

    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target): void
    {
    }

    public function supportsWebDirAccess(): bool
    {
        return false;
    }

    public function getStringIdentifier(): string
    {
        // TODO: Implement getSubmissionStringIdentifier() method.
        return "";
    }

    public function getExportObjIdForResourceId(int $resource_id): int
    {
        return 0;
    }
}
