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
 ********************************************************************
 */
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnitListGUI
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilObjOrgUnitListGUI extends ilObjectListGUI
{
    protected ilLanguage $lng;
    protected ilSetting $settings;


    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
    }

    /**
     * initialisation
     */
    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;

        $this->type = "orgu";
        $this->gui_class_name = "ilobjorgunitgui";

        // general commands array
        $this->commands = ilObjOrgUnitAccess::_getCommands();
    }

    /**
     * no timing commands needed in orgunits.
     */
    public function insertTimingsCommand(): void
    {
    }

    /**
     * no social commands needed in orgunits.
     */
    public function insertCommonSocialCommands(bool $a_header_actions = false): void
    {
    }

    /**
     * insert info screen command
     */
    public function insertInfoScreenCommand(): void
    {
        if ($this->std_cmd_only) {
            return;
        }
        $cmd_link = $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
        $cmd_frame = $this->getCommandFrame("infoScreen");

        $this->insertCommand(
            $cmd_link,
            $this->lng->txt("info_short"),
            $cmd_frame,
            ilUtil::getImagePath("icon_info.svg")
        );
    }

    public function getCommandLink(string $a_cmd): string
    {
        $this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $this->ref_id);

        return $this->ctrl->getLinkTargetByClass("ilobjorgunitgui", $a_cmd);
    }

    /**
     * Get object type specific type icon
     */
    public function getTypeIcon(): string
    {
        $icons_cache = ilObjOrgUnit::getIconsCache();
        if (isset($icons_cache[$this->obj_id])) {
            $icon_file = $icons_cache[$this->obj_id];
            return $icon_file;
        }

        return ilObject::getIconForReference(
            $this->ref_id,
            $this->obj_id,
            'small',
            $this->getIconImageType()
        );
    }
}
