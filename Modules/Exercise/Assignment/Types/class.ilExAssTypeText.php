<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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

    public function isActive() : bool
    {
        return true;
    }

    public function usesTeams() : bool
    {
        return false;
    }

    public function usesFileUpload() : bool
    {
        return false;
    }

    public function getTitle() : string
    {
        $lng = $this->lng;

        return $lng->txt("exc_type_text");
    }

    public function getSubmissionType() : string
    {
        return ilExSubmission::TYPE_TEXT;
    }

    public function isSubmissionAssignedToTeam() : bool
    {
        return false;
    }

    public function cloneSpecificProperties(ilExAssignment $source, ilExAssignment $target) : void
    {
    }

    public function supportsWebDirAccess() : bool
    {
        return false;
    }

    public function getStringIdentifier() : string
    {
        // TODO: Implement getSubmissionStringIdentifier() method.
        return "";
    }

    public function getExportObjIdForResourceId(int $resource_id) : int
    {
        return 0;
    }
}
