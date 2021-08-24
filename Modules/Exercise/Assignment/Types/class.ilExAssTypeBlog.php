<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Blog type
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssTypeBlog implements ilExAssignmentTypeInterface
{
    protected ilSetting $setting;
    protected ilLanguage $lng;

    /**
     * Constructor
     *
     * @param ilSetting|null $a_setting
     * @param ilLanguage|null $a_lng
     */
    public function __construct(ilSetting $a_setting = null, ilLanguage $a_lng = null)
    {
        global $DIC;

        $this->setting = ($a_setting)
            ?: $DIC["ilSetting"];

        $this->lng = ($a_lng)
            ?: $DIC->language();
    }

    public function isActive() : bool
    {
        if ($this->setting->get('disable_wsp_blogs')) {
            return false;
        }
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

        return $lng->txt("exc_type_blog");
    }

    public function getSubmissionType() : string
    {
        return ilExSubmission::TYPE_OBJECT;
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
}
