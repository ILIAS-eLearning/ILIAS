<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio type
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilExAssTypePortfolio implements ilExAssignmentTypeInterface
{
    protected const STR_IDENTIFIER = "prtf";

    protected ilSetting $setting;
    protected ilLanguage $lng;
    protected string $identifier_str;

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
        if ($this->setting->get('user_portfolios')) {
            return true;
        }
        return false;
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

        return $lng->txt("exc_type_portfolio");
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

    public function getExportObjIdForResourceId(int $resource_id) : int
    {
        return $resource_id;
    }
}
