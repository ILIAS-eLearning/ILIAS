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

    public function isActive(): bool
    {
        if ($this->setting->get('disable_wsp_blogs')) {
            return false;
        }
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

        return $lng->txt("exc_type_blog");
    }

    public function getSubmissionType(): string
    {
        return ilExSubmission::TYPE_OBJECT;
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
        return true;
    }

    public function getStringIdentifier(): string
    {
        return self::STR_IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function getExportObjIdForResourceId(int $resource_id): int
    {
        // in case of blogs the $resource id is the workspace id
        $tree = new ilWorkspaceTree(0);
        $owner = $tree->lookupOwner($resource_id);
        $tree = new ilWorkspaceTree($owner);
        return $tree->lookupObjectId($resource_id);
    }
}
