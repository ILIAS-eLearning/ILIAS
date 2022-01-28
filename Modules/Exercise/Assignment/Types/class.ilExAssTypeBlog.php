<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Blog type
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExAssTypeBlog implements ilExAssignmentTypeInterface
{
    protected const STR_IDENTIFIER = "blog";

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
        return true;
    }

    public function getStringIdentifier() : string
    {
        return self::STR_IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function getExportObjIdForResourceId(int $resource_id) : int
    {
        // in case of blogs the $resource id is the workspace id
        $tree = new ilWorkspaceTree(0);
        $owner = $tree->lookupOwner($resource_id);
        $tree = new ilWorkspaceTree($owner);
        return $tree->lookupObjectId($resource_id);
    }
}
