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

    public function insertIconsAndCheckboxes(): void
    {

        // FSX removed $this->getCheckboxStatus() in if-Statement: 0014726
        if (!$this->settings->get('custom_icons')) {
            parent::insertIconsAndCheckboxes();

            return;
        }
        $icons_cache = ilObjOrgUnit::getIconsCache();
        if (isset($icons_cache[$this->obj_id])) {
            $icon_file = $icons_cache[$this->obj_id];
            // icon link
            if (!$this->default_command or (!$this->getCommandsStatus() and !$this->restrict_to_goto)) {
            } else {
                $this->tpl->setCurrentBlock("icon_link_s");

                if ($this->default_command["frame"] != "") {
                    $this->tpl->setVariable("ICON_TAR", "target='" . $this->default_command["frame"] . "'");
                }

                $this->tpl->setVariable("ICON_HREF", $this->default_command["link"]);
                $this->tpl->parseCurrentBlock();
                $this->tpl->touchBlock("icon_link_e");
            }
            $this->enableIcon(false);
            if ($this->getContainerObject()->isActiveAdministrationPanel() && !$_SESSION["clipboard"]) {
                $this->tpl->touchBlock("i_1");    // indent main div  }
                $this->tpl->touchBlock("d_2");    // indent main div  } #0014913
            } else {
                $this->tpl->touchBlock("d_1");
            }

            parent::insertIconsAndCheckboxes();
            $this->tpl->setCurrentBlock("icon");
            $this->tpl->setVariable(
                "ALT_ICON",
                $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $this->getIconImageType())
            );
            $this->tpl->setVariable("SRC_ICON", $icon_file);
            $this->tpl->parseCurrentBlock();
            $this->enableIcon(true);
        } else {
            parent::insertIconsAndCheckboxes();
        }
    }
}
